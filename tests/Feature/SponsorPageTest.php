<?php

namespace Tests\Feature;

use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorPageTest extends TestCase
{
    use RefreshDatabase;

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

    /** Fills every spot on both sides. */
    private function fillEverySpot(): void
    {
        $perRail = (int) config('social.sponsors.slots_per_rail');

        $taken = 0;

        foreach (['left', 'right'] as $side) {
            for ($i = 0; $i < $perRail; $i++) {
                $this->slot([
                    'name' => ucfirst($side).' '.$i,
                    'ends_at' => now()->addDays(10 + $taken++),
                ]);
            }
        }
    }

    public function test_an_open_slot_card_leads_to_the_sponsor_page(): void
    {
        config(['social.sponsors.slots_per_rail' => 4, 'social.sponsors.price' => 250]);

        $this->get('/')->assertOk()->assertSee(route('sponsor'), false);
        $this->get(route('sponsor'))->assertOk();
    }

    public function test_the_page_offers_the_spots_that_are_free(): void
    {
        config(['social.sponsors.slots_per_rail' => 4, 'social.sponsors.price' => 250, 'social.sponsors.days' => 30]);

        $this->slot();

        $this->get(route('sponsor'))->assertOk()
            ->assertSee('7 of 8 spots open')
            ->assertSee('Continue to payment')
            ->assertSee('€250')
            ->assertSee('30 days');
    }

    public function test_a_sold_out_page_sells_the_next_spot_and_names_the_date(): void
    {
        config([
            'social.sponsors.slots_per_rail' => 2,
            'social.sponsors.price' => 250,
            'social.sponsors.advance_price' => 999,
        ]);

        $this->fillEverySpot();

        $this->get(route('sponsor'))->assertOk()
            ->assertSee('All 4 spots are taken')
            ->assertSee('you take the first spot that comes free')
            ->assertSee('€999')
            ->assertSee(now()->addDays(10)->format('j F Y'))
            ->assertDontSee('Continue to payment');
    }

    public function test_the_next_spot_is_offered_even_without_an_advance_price(): void
    {
        config([
            'social.sponsors.slots_per_rail' => 1,
            'social.sponsors.price' => 250,
            'social.sponsors.advance_price' => 0,
        ]);

        $this->fillEverySpot();

        $this->get(route('sponsor'))->assertOk()
            ->assertSee('Take the next spot')
            ->assertSee('you take the first spot that comes free')
            ->assertDontSee('€999');
    }

    public function test_a_full_rail_still_links_through_to_the_page(): void
    {
        config(['social.sponsors.slots_per_rail' => 1, 'social.sponsors.price' => 250]);

        $this->fillEverySpot();

        $this->get('/')->assertOk()
            ->assertDontSee('Open slot')
            ->assertSee('Sponsor this site');
    }

    public function test_the_page_says_so_when_nothing_is_for_sale(): void
    {
        config(['social.sponsors.price' => 0]);

        $this->get(route('sponsor'))->assertOk()
            ->assertSee('Not for sale right now')
            ->assertDontSee('Continue to payment');
    }
}
