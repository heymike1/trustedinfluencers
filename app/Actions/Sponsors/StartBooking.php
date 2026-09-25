<?php

namespace App\Actions\Sponsors;

use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\DB;

/**
 * Opens a checkout for one spot. The spot is held for the length of the hold so two people cannot
 * pay for the same card; when nothing is free the booking joins the queue instead.
 */
class StartBooking
{
    public function handle(string $email, ?string $spotKey = null): SponsorSlot
    {
        return DB::transaction(function () use ($email, $spotKey) {
            // Read the spots inside the transaction: whoever gets here first takes the spot.
            $spot = Sponsorship::parseSpot($spotKey);

            if ($spot && ! Sponsorship::isSpotOpen($spotKey)) {
                $spot = null;
            }

            // Asked for nothing in particular, or asked for one that just went: take any free spot.
            if (! $spot) {
                $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first());
            }

            return SponsorSlot::create([
                'status' => SponsorSlot::PENDING,
                'buyer_email' => $email,
                'side' => $spot[0] ?? 'left',
                'position' => $spot[1] ?? null,
                'reserved_until' => now()->addMinutes(Sponsorship::holdMinutes()),
                'amount' => $spot ? Sponsorship::amount() : Sponsorship::amountDueNow(),
                'currency' => Sponsorship::currency(),
                'tint' => array_rand(SponsorSlot::TINTS),
                'is_active' => true,
            ]);
        });
    }
}
