<?php

namespace App\Actions\Sponsors;

use App\Mail\SponsorEndingSoon;
use App\Mail\SponsorSpotReady;
use App\Models\SponsorSlot;
use App\Support\Sponsorship;
use Illuminate\Support\Facades\Mail;

/**
 * Keeps the rails moving: holds that ran out are released, finished runs are closed, and the spot
 * that comes free goes to whoever has been waiting longest. Runs on a schedule.
 *
 * @return array{released: int, ended: int, promoted: int, warned: int}
 */
class RollBookings
{
    public function handle(): array
    {
        return [
            'released' => $this->releaseExpiredHolds(),
            'ended' => $this->endFinishedRuns(),
            'promoted' => $this->fillFreeSpotsFromTheQueue(),
            'warned' => $this->warnAboutRunsEndingSoon(),
        ];
    }

    /** A checkout nobody finished stops holding its spot. */
    private function releaseExpiredHolds(): int
    {
        return SponsorSlot::where('status', SponsorSlot::PENDING)
            ->whereNotNull('reserved_until')
            ->where('reserved_until', '<=', now())
            ->update(['status' => SponsorSlot::CANCELLED, 'position' => null, 'reserved_until' => null]);
    }

    private function endFinishedRuns(): int
    {
        $ended = 0;

        SponsorSlot::where('status', SponsorSlot::LIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get()
            ->each(function (SponsorSlot $booking) use (&$ended) {
                $booking->forceFill(['status' => SponsorSlot::ENDED, 'position' => null])->save();
                $ended++;
            });

        return $ended;
    }

    /** First paid, first served. */
    private function fillFreeSpotsFromTheQueue(): int
    {
        $promoted = 0;

        foreach (SponsorSlot::queued()->get() as $booking) {
            $spot = Sponsorship::parseSpot(Sponsorship::openSpots()->first());

            if (! $spot) {
                break;
            }

            $booking->forceFill([
                'side' => $spot[0],
                'position' => $spot[1],
                'queued_at' => null,
                'token' => $booking->freshToken(),
            ])->save();

            // They paid before there was anywhere to go; now there is.
            Mail::to($booking->buyer_email)->send(new SponsorSpotReady($booking));
            $promoted++;
        }

        return $promoted;
    }

    /** One heads-up before a run ends. Nothing renews by itself, so this is the only nudge. */
    private function warnAboutRunsEndingSoon(): int
    {
        $warned = 0;

        SponsorSlot::where('status', SponsorSlot::LIVE)
            ->whereNull('ending_notice_at')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->addDays(3))
            ->where('ends_at', '>', now())
            ->get()
            ->each(function (SponsorSlot $booking) use (&$warned) {
                Mail::to($booking->buyer_email)->send(new SponsorEndingSoon($booking));
                $booking->forceFill(['ending_notice_at' => now()])->save();
                $warned++;
            });

        return $warned;
    }
}
