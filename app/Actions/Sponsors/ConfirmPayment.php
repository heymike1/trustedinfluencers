<?php

namespace App\Actions\Sponsors;

use App\Mail\SponsorBookingPaid;
use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The money is in, so the spot is handed out now: the one they picked if it is still free, the
 * first free one otherwise, and the queue when the rails are full. Safe to call twice: the
 * webhook and the buyer coming back both land here.
 */
class ConfirmPayment
{
    public function handle(SponsorSlot $booking, ?string $reference = null): SponsorSlot
    {
        if ($booking->paid_at !== null) {
            return $booking;
        }

        DB::transaction(function () use ($booking, $reference) {
            $wanted = $booking->position !== null && Sponsorship::isSpotOpen($booking->spotKey())
                ? [$booking->side, $booking->position]
                : Sponsorship::parseSpot(Sponsorship::openSpots()->first());

            $booking->forceFill([
                'status' => SponsorSlot::PAID,
                'side' => $wanted[0] ?? $booking->side,
                'position' => $wanted[1] ?? null,
                'paid_at' => now(),
                'queued_at' => $wanted === null ? now() : null,
                'payment_reference' => $reference ?? $booking->payment_reference,
                'token' => $booking->freshToken(),
            ])->save();
        });

        Mail::to($booking->buyer_email)->send(new SponsorBookingPaid($booking->fresh()));

        return $booking->fresh();
    }
}
