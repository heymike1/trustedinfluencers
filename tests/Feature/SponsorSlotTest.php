<?php

namespace Tests\Feature;

use App\Livewire\Admin\Sponsors;
use App\Models\SponsorSlot;
use App\Models\User;
use App\Support\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SponsorSlotTest extends TestCase
{
    use RefreshDatabase;

    /** A card that is up, in the first spot nobody has taken. */
    private function slot(array $attributes = []): SponsorSlot
    {
        $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first()) ?? ['left', 1];

        return SponsorSlot::create(array_merge([
            'name' => 'Blotato',
            'tagline' => 'Social media API',
            'url' => 'https://blotato.example',
            'side' => $spot[0],
            'position' => $spot[1],
            'status' => SponsorSlot::LIVE,
        ], $attributes));
    }

    public function test_only_live_slots_are_rendered(): void
    {
        $this->slot(['name' => 'Showing']);
        $this->slot(['name' => 'Paused', 'is_active' => false]);
        $this->slot(['name' => 'Scheduled', 'starts_at' => now()->addWeek()]);
        $this->slot(['name' => 'Ended', 'ends_at' => now()->subDay()]);

        $this->get('/')->assertOk()
            ->assertSee('Showing')
            ->assertDontSee('Paused')
            ->assertDontSee('Scheduled')
            ->assertDontSee('Ended');
    }

    public function test_every_free_slot_gets_its_own_card(): void
    {
        config(['social.sponsors.slots_per_rail' => 4]);

        // Nothing booked: four cards a side, eight in total.
        $this->assertSame(8, substr_count($this->get('/')->assertOk()->getContent(), 'Open slot'));

        $this->slot(['side' => 'left']);
        $this->assertSame(7, substr_count($this->get('/')->getContent(), 'Open slot'));
    }

    public function test_the_rails_are_absent_when_the_price_is_switched_off(): void
    {
        config(['social.sponsors.price' => 0]);

        $this->get('/')->assertOk()->assertDontSee('aria-label="Sponsored"', false);
    }

    public function test_a_click_is_counted_and_redirected(): void
    {
        $slot = $this->slot();

        $this->get(route('sponsors.click', $slot))->assertRedirect('https://blotato.example');
        $this->assertSame(1, $slot->fresh()->clicks);
    }

    public function test_a_paused_slot_cannot_be_clicked(): void
    {
        $slot = $this->slot(['is_active' => false]);

        $this->get(route('sponsors.click', $slot))->assertNotFound();
        $this->assertSame(0, $slot->fresh()->clicks);
    }

    public function test_admins_can_book_pause_and_remove_a_sponsor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(Sponsors::class)
            ->set('form.name', 'Chargeback.io')
            ->set('form.tagline', 'Prevent chargebacks on autopilot')
            ->set('form.url', 'https://chargeback.example')
            ->set('form.side', 'right')
            ->set('form.position', 1)
            ->call('save')
            ->assertHasNoErrors();

        $slot = SponsorSlot::sole();
        $this->assertSame('right', $slot->side);
        $this->assertSame('Live', $slot->state());

        Livewire::actingAs($admin)->test(Sponsors::class)->call('toggle', $slot->id);
        $this->assertSame('Paused', $slot->fresh()->state());

        Livewire::actingAs($admin)->test(Sponsors::class)->call('destroy', $slot->id);
        $this->assertSame(0, SponsorSlot::count());
    }

    public function test_the_end_date_must_come_after_the_start(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(Sponsors::class)
            ->set('form.name', 'Blotato')
            ->set('form.tagline', 'Social media API')
            ->set('form.url', 'https://blotato.example')
            ->set('form.starts_at', now()->addWeek()->format('Y-m-d'))
            ->set('form.ends_at', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasErrors('form.ends_at');
    }

    public function test_an_open_slot_is_offered_until_the_rail_is_full(): void
    {
        config(['social.sponsors.slots_per_rail' => 2, 'social.sponsors.price' => 250]);

        $this->slot(['name' => 'Blotato']);
        $this->slot(['name' => 'Chargeback']);
        $this->get('/')->assertOk()->assertSee('Open slot')->assertSee('€250 / 30 days');

        // Every spot taken: nothing left to sell, so the card is gone.
        $this->slot(['name' => 'Libertus']);
        $this->slot(['name' => 'Postiz']);
        $this->get('/')->assertOk()->assertDontSee('Open slot');
    }

    public function test_the_open_slot_card_is_off_without_a_price(): void
    {
        config(['social.sponsors.price' => 0]);
        $this->slot();

        $this->get('/')->assertOk()->assertSee('Blotato')->assertDontSee('Open slot');
    }

    public function test_the_admin_screen_is_closed_to_everyone_else(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.sponsors'))->assertForbidden();
    }
}
