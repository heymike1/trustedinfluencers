<?php

namespace App\Actions\Sponsors;

use App\Models\SponsorSlot;
use App\Support\Sponsorship;

/**
 * Opens a checkout. Nothing is held: the spot is handed out when the money is in, so an
 * abandoned checkout costs nobody anything. The spot they clicked is remembered as a preference
 * and honoured if it is still free by then.
 */
class StartBooking
{
    public function handle(string $email, ?string $spotKey = null): SponsorSlot
    {
        $spot = Sponsorship::parseSpot($spotKey);

        return SponsorSlot::create([
            'status' => SponsorSlot::PENDING,
            'buyer_email' => $email,
            'side' => $spot[0] ?? 'left',
            'position' => $spot[1] ?? null,
            'amount' => Sponsorship::amountDueNow(),
            'currency' => Sponsorship::currency(),
            'tint' => array_rand(SponsorSlot::TINTS),
            'is_active' => true,
        ]);
    }
}
