<?php

namespace App\Sponsors;

use App\Models\SponsorSlot;

/**
 * Local stand-in for the real thing: a page with a Pay button, so the whole flow can be walked
 * through without a payment provider. Never bound outside local and testing.
 */
class FakeCheckout implements CheckoutGateway
{
    public function start(SponsorSlot $booking, string $returnUrl, string $cancelUrl): string
    {
        $booking->update(['checkout_session_id' => 'fake_'.$booking->id]);

        return route('sponsor.checkout.fake', $booking);
    }

    public function isPaid(SponsorSlot $booking): ?bool
    {
        return $booking->paid_at !== null;
    }
}
