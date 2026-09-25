<?php

namespace Tests\Feature;

use App\Actions\Sponsors\RollBookings;
use App\Livewire\SponsorCard;
use App\Mail\SponsorBookingPaid;
use App\Mail\SponsorCardLive;
use App\Mail\SponsorSpotReady;
use App\Models\SponsorSlot;
use App\Sponsors\StripeCheckout;
use App\Support\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SponsorCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['social.sponsors.slots_per_rail' => 2, 'social.sponsors.price' => 250, 'social.sponsors.advance_price' => 999]);
        Mail::fake();
    }

    private function buy(?string $spot = null, string $email = 'buyer@example.com'): SponsorSlot
    {
        $this->post(route('sponsor.checkout'), ['email' => $email, 'spot' => $spot]);

        return SponsorSlot::where('buyer_email', $email)->latest('id')->firstOrFail();
    }

    public function test_starting_a_checkout_holds_the_spot_that_was_clicked(): void
    {
        $booking = $this->buy('right2');

        $this->assertSame('right', $booking->side);
        $this->assertSame(2, $booking->position);
        $this->assertSame(SponsorSlot::PENDING, $booking->status);
        $this->assertTrue($booking->reserved_until->isFuture());

        // Nobody else is offered that spot while the checkout is open.
        $this->get(route('sponsor', ['spot' => 'right2']))->assertOk()->assertSee('That one just went');
    }

    public function test_two_buyers_cannot_end_up_in_the_same_spot(): void
    {
        $first = $this->buy('left1', 'one@example.com');
        $second = $this->buy('left1', 'two@example.com');

        $this->assertSame('left1', $first->spotKey());
        $this->assertNotSame('left1', $second->spotKey());
    }

    public function test_paying_confirms_the_booking_and_hands_over_the_card_page(): void
    {
        $booking = $this->buy('left1');

        $this->post(route('sponsor.checkout.fake.pay', $booking))
            ->assertRedirect(route('sponsor.card', $booking->fresh()->token));

        $booking->refresh();
        $this->assertSame(SponsorSlot::PAID, $booking->status);
        $this->assertNotNull($booking->paid_at);
        $this->assertNotNull($booking->token);
        Mail::assertQueued(SponsorBookingPaid::class);
    }

    public function test_the_card_goes_up_when_the_buyer_fills_it_in(): void
    {
        $booking = $this->buy('left1');
        $this->post(route('sponsor.checkout.fake.pay', $booking));

        Livewire::test(SponsorCard::class, ['token' => $booking->fresh()->token])
            ->set('name', 'Blotato')
            ->set('url', 'https://blotato.example')
            ->set('tagline', 'Social media API')
            ->call('save')
            ->assertHasNoErrors();

        $booking->refresh();
        $this->assertSame(SponsorSlot::LIVE, $booking->status);
        $this->assertTrue($booking->starts_at->isToday());
        $this->assertSame(30, (int) $booking->starts_at->diffInDays($booking->ends_at));
        Mail::assertQueued(SponsorCardLive::class);

        $this->get('/')->assertOk()->assertSee('Blotato');
    }

    public function test_a_card_page_needs_the_token(): void
    {
        $this->get(route('sponsor.card', 'not-a-real-token'))->assertNotFound();
    }

    public function test_buying_while_everything_is_taken_puts_you_in_the_queue(): void
    {
        SponsorSlot::factory()->count(4)->create();

        $booking = $this->buy();
        $this->post(route('sponsor.checkout.fake.pay', $booking));
        $booking->refresh();

        $this->assertTrue($booking->isQueued());
        $this->assertNull($booking->position);
        // The advance is what a full site charges.
        $this->assertSame(999, $booking->amount);
    }

    public function test_a_spot_that_comes_free_goes_to_whoever_paid_first(): void
    {
        $running = SponsorSlot::factory()->create(['ends_at' => now()->subMinute()]);
        SponsorSlot::factory()->count(3)->create();

        $early = SponsorSlot::factory()->queued()->create(['paid_at' => now()->subDay(), 'buyer_email' => 'early@example.com']);
        $late = SponsorSlot::factory()->queued()->create(['paid_at' => now(), 'buyer_email' => 'late@example.com']);

        app(RollBookings::class)->handle();

        $this->assertSame(SponsorSlot::ENDED, $running->fresh()->status);
        $this->assertSame($running->side.$running->position, $early->fresh()->spotKey());
        $this->assertNull($late->fresh()->position);
        Mail::assertQueued(SponsorSpotReady::class, 1);
    }

    public function test_a_checkout_nobody_finished_lets_go_of_its_spot(): void
    {
        $abandoned = SponsorSlot::factory()->pending()->create(['reserved_until' => now()->subMinute()]);

        app(RollBookings::class)->handle();

        $this->assertSame(SponsorSlot::CANCELLED, $abandoned->fresh()->status);
        $this->assertTrue(Sponsorship::isSpotOpen('left1'));
    }

    public function test_the_webhook_only_listens_to_stripe(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $booking = $this->buy('left1');

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['payment_status' => 'paid', 'payment_intent' => 'pi_123', 'metadata' => ['booking' => (string) $booking->id]]],
        ]);

        $this->call('POST', route('webhooks.stripe'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=nonsense'], $payload)
            ->assertStatus(400);
        $this->assertNull($booking->fresh()->paid_at);

        $now = time();
        $signature = hash_hmac('sha256', $now.'.'.$payload, 'whsec_test');
        $this->call('POST', route('webhooks.stripe'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't='.$now.',v1='.$signature], $payload)
            ->assertOk();

        $this->assertNotNull($booking->fresh()->paid_at);
        $this->assertSame('pi_123', $booking->fresh()->payment_reference);
    }

    public function test_a_stale_signature_is_refused(): void
    {
        $payload = '{}';
        $old = time() - 3600;

        $this->assertFalse(StripeCheckout::verifySignature(
            $payload,
            't='.$old.',v1='.hash_hmac('sha256', $old.'.'.$payload, 'whsec_test'),
            'whsec_test',
        ));
    }
}
