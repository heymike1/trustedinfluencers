<?php

namespace App\Actions\Sponsors;

use App\Mail\SponsorCardLive;
use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\Mail;

/**
 * Puts a paid booking up in the rail. This is the moment the clock starts: the run is counted from
 * the day the card appears, not from the day it was paid for.
 */
class PublishCard
{
    public function handle(SponsorSlot $booking): SponsorSlot
    {
        if (! $booking->hasCard() || $booking->position === null || $booking->status !== SponsorSlot::PAID) {
            return $booking;
        }

        $booking->forceFill([
            'status' => SponsorSlot::LIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(Sponsorship::days()),
        ])->save();

        Mail::to($booking->buyer_email)->send(new SponsorCardLive($booking));

        return $booking;
    }
}
