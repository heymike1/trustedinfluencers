<?php

namespace App\Sponsors;

use App\Models\SponsorSlot;

/**
 * Hands a buyer off to wherever they pay. The app only ever needs somewhere to send them.
 */
interface CheckoutGateway
{
    /** The URL to send the buyer to, for this booking. */
    public function start(SponsorSlot $booking, string $returnUrl, string $cancelUrl): string;

    /**
     * Whether this checkout is paid, asked straight at the source. Used when the buyer comes back
     * before the webhook has landed.
     *
     * @return bool|null null when we cannot tell and should wait for the webhook
     */
    public function isPaid(SponsorSlot $booking): ?bool;
}
