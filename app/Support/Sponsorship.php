<?php

namespace App\Support;

use App\Models\SponsorSlot;
use Illuminate\Support\Carbon;

/**
 * What is for sale in the two rails beside the page, and what it costs. One booking runs for a
 * fixed number of days from the day it goes live, so the pages and the sponsor page agree on
 * how many spots are open without either counting slots itself.
 */
class Sponsorship
{
    /** Spots we sell in total: both rails together. */
    public static function total(): int
    {
        return (int) config('social.sponsors.slots_per_rail') * 2;
    }

    /** Spots running right now. */
    public static function taken(): int
    {
        return min(SponsorSlot::live()->count(), self::total());
    }

    public static function open(): int
    {
        return max(0, self::total() - self::taken());
    }

    public static function isFull(): bool
    {
        return self::open() === 0;
    }

    /** Off entirely when there is no price: the rails go quiet and nothing is offered. */
    public static function isForSale(): bool
    {
        return (bool) self::price();
    }

    public static function price(): ?string
    {
        return config('social.sponsors.price') ?: null;
    }

    public static function advancePrice(): ?string
    {
        return config('social.sponsors.advance_price') ?: null;
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

    public static function contact(): string
    {
        return (string) config('social.sponsors.contact');
    }

    /**
     * When the first booked spot frees up. Null when a spot is open-ended, because then there is
     * no date to promise.
     */
    public static function nextFreeAt(): ?Carbon
    {
        $live = SponsorSlot::live()->get();

        if ($live->isEmpty() || $live->contains(fn (SponsorSlot $slot) => $slot->ends_at === null)) {
            return null;
        }

        return $live->min('ends_at');
    }

    /** A mailto for the enquiry, pre-filled so we know which of the two it is. */
    public static function mailto(string $subject): string
    {
        return 'mailto:'.self::contact().'?subject='.rawurlencode($subject.' '.config('app.name'));
    }
}
