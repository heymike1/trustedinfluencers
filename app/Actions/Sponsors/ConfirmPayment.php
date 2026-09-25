<?php

namespace App\Actions\Sponsors;

use App\Mail\SponsorBookingPaid;
use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The money is in. The booking keeps the spot it was holding, or takes another one if that spot
 * went while the buyer was paying, or joins the queue when the rails are full. Safe to call twice:
 * the webhook and the buyer coming back both land here.
 */
class ConfirmPayment
{
    public function handle(SponsorSlot $booking, ?string $reference = null): SponsorSlot
    {
        if ($booking->paid_at !== null) {
            return $booking;
        }

        DB::transaction(function () use ($booking, $reference) {
            $position = $booking->position;

            // The hold may have run out while they were paying. Then it is the next free spot,
            // and failing that the queue, exactly as the page promises.
            if ($position === null || ! $this->stillOurs($booking)) {
                $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first());
                $booking->side = $spot[0] ?? $booking->side;
                $position = $spot[1] ?? null;
            }

            $booking->forceFill([
                'status' => SponsorSlot::PAID,
                'position' => $position,
                'paid_at' => now(),
                'queued_at' => $position === null ? now() : null,
                'reserved_until' => null,
                'payment_reference' => $reference ?? $booking->payment_reference,
                'token' => $booking->freshToken(),
            ])->save();
        });

        Mail::to($booking->buyer_email)->send(new SponsorBookingPaid($booking->fresh()));

        return $booking->fresh();
    }

    /** Nobody else has taken the spot this booking was holding. */
    private function stillOurs(SponsorSlot $booking): bool
    {
        return ! SponsorSlot::holdingASpot()
            ->where('id', '!=', $booking->id)
            ->where('side', $booking->side)
            ->where('position', $booking->position)
            ->exists();
    }
}
