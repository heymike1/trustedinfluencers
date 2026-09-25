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

    public function test_an_open_checkout_keeps_nobody_out(): void
    {
        $booking = $this->buy('right2');

        $this->assertSame(SponsorSlot::PENDING, $booking->status);

        // Until it is paid for, the spot is still on offer to everyone else.
        $this->assertTrue(Sponsorship::isSpotOpen('right2'));
        $this->get(route('sponsor', ['spot' => 'right2']))->assertOk()->assertDontSee('That one just went');
    }

    public function test_the_spot_goes_to_whoever_pays_first(): void
    {
        $first = $this->buy('left1', 'one@example.com');
        $second = $this->buy('left1', 'two@example.com');

        // Both wanted left1; the one who pays first gets it and the other moves along.
        $this->post(route('sponsor.checkout.fake.pay', $second));
        $this->post(route('sponsor.checkout.fake.pay', $first));

        $this->assertSame('left1', $second->fresh()->spotKey());
        $this->assertNotSame('left1', $first->fresh()->spotKey());
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
        // The logo comes along with the domain they gave us.
        $this->assertSame('https://www.google.com/s2/favicons?domain=blotato.example&sz=64', $booking->logo_url);
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

    public function test_a_checkout_nobody_finished_is_cleared_away_after_a_day(): void
    {
        $fresh = SponsorSlot::factory()->pending()->create();
        $stale = SponsorSlot::factory()->pending()->create(['created_at' => now()->subDays(2)]);

        app(RollBookings::class)->handle();

        $this->assertSame(SponsorSlot::PENDING, $fresh->fresh()->status);
        $this->assertSame(SponsorSlot::CANCELLED, $stale->fresh()->status);
    }

    public function test_a_card_filled_in_from_the_queue_goes_up_the_moment_a_spot_frees(): void
    {
        SponsorSlot::factory()->create(['ends_at' => now()->subMinute()]);
        SponsorSlot::factory()->count(3)->create();

        $waiting = SponsorSlot::factory()->queued()->create([
            'name' => 'Blotato',
            'tagline' => 'Social media API',
            'url' => 'https://blotato.example',
        ]);

        app(RollBookings::class)->handle();

        $this->assertSame(SponsorSlot::LIVE, $waiting->fresh()->status);
        $this->assertTrue($waiting->fresh()->starts_at->isToday());
        $this->get('/')->assertOk()->assertSee('Blotato');
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
