<?php

namespace App\Support;

use App\Models\SponsorSlot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The two rails hold a fixed number of numbered spots: left1 … left4 and right1 … right4. A spot
 * is open when no booking holds it, and a booking holds it while its card is up, while the money
 * is in, or while a checkout is still running. Everything that asks "what is for sale" asks here.
 */
class Sponsorship
{
    public static function perRail(): int
    {
        return max(0, (int) config('social.sponsors.slots_per_rail'));
    }

    /** Spots we sell in total: both rails together. */
    public static function total(): int
    {
        return self::perRail() * 2;
    }

    /** Every spot, in rail order, with the booking that holds it (or null). */
    public static function spots(): Collection
    {
        $held = SponsorSlot::holdingASpot()->get()->keyBy(fn (SponsorSlot $slot) => $slot->spotKey());

        return collect(['left', 'right'])
            ->crossJoin(range(1, max(1, self::perRail())))
            ->when(self::perRail() === 0, fn (Collection $c) => collect())
            ->map(fn (array $pair) => [
                'side' => $pair[0],
                'position' => $pair[1],
                'key' => $pair[0].$pair[1],
                'booking' => $held->get($pair[0].$pair[1]),
            ])
            ->values();
    }

    /** @return Collection<int, string> The keys of the spots nobody holds. */
    public static function openSpots(): Collection
    {
        return self::spots()->whereNull('booking')->pluck('key');
    }

    public static function isSpotOpen(string $key): bool
    {
        return self::openSpots()->contains($key);
    }

    /** "left1" → ['left', 1], or null when it is not a spot we sell. */
    public static function parseSpot(?string $key): ?array
    {
        if (! $key || ! preg_match('/^(left|right)([1-9]\d?)$/', $key, $m)) {
            return null;
        }

        return (int) $m[2] <= self::perRail() ? [$m[1], (int) $m[2]] : null;
    }

    public static function taken(): int
    {
        return self::total() - self::open();
    }

    public static function open(): int
    {
        return self::openSpots()->count();
    }

    public static function isFull(): bool
    {
        return self::total() > 0 && self::open() === 0;
    }

    /** Off entirely when there is no price: the rails go quiet and nothing is offered. */
    public static function isForSale(): bool
    {
        return self::total() > 0 && self::amount() > 0;
    }

    /** People who paid while everything was taken and are waiting for a spot. */
    public static function queueLength(): int
    {
        return SponsorSlot::queued()->count();
    }

    public static function amount(): int
    {
        return max(0, (int) config('social.sponsors.price'));
    }

    public static function advanceAmount(): int
    {
        return max(0, (int) config('social.sponsors.advance_price'));
    }

    /** What this buyer pays: the advance when there is nothing free to take today. */
    public static function amountDueNow(): int
    {
        return self::isFull() && self::advanceAmount() > 0 ? self::advanceAmount() : self::amount();
    }

    public static function currency(): string
    {
        return strtolower((string) config('social.sponsors.currency')) ?: 'eur';
    }

    /** "€250". Falls back to "250 SEK" for anything without a symbol we know. */
    public static function money(?int $amount): ?string
    {
        if (! $amount) {
            return null;
        }

        return match (self::currency()) {
            'eur' => '€'.number_format($amount, 0, ',', '.'),
            'usd' => '$'.number_format($amount),
            'gbp' => '£'.number_format($amount),
            default => number_format($amount).' '.strtoupper(self::currency()),
        };
    }

    public static function price(): ?string
    {
        return self::money(self::amount());
    }

    public static function advancePrice(): ?string
    {
        return self::money(self::advanceAmount());
    }

    public static function days(): int
    {
        return max(1, (int) config('social.sponsors.days'));
    }

    /** "30 days", for the price line on the cards. */
    public static function periodLabel(): string
    {
        return self::days().' days';
    }

    public static function holdMinutes(): int
    {
        return max(1, (int) config('social.sponsors.hold_minutes'));
    }

    public static function contact(): string
    {
        return (string) config('social.sponsors.contact');
    }

    /**
     * When the first booked spot frees up. Null when a live card has no end date, because then
     * there is no date to promise.
     */
    public static function nextFreeAt(): ?Carbon
    {
        $live = SponsorSlot::live()->get();

        if ($live->isEmpty() || $live->contains(fn (SponsorSlot $slot) => $slot->ends_at === null)) {
            return null;
        }

        // Everyone already in the queue is ahead of this buyer.
        return $live->sortBy('ends_at')->values()->get(self::queueLength())?->ends_at;
    }

    /** A mailto for the enquiry, pre-filled so we know what it is about. */
    public static function mailto(string $subject): string
    {
        return 'mailto:'.self::contact().'?subject='.rawurlencode($subject.' '.config('app.name'));
    }
}
