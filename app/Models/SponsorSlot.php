<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One booking of one numbered spot in one of the two rails beside the page.
 *
 * pending  a checkout is open; nothing is reserved and no spot is taken yet
 * paid     the money is in and the spot is theirs. With a position it is waiting for the buyer's
 *          card details, without one it is in the queue for the first spot that comes free
 * live     the card is up and the clock is running
 * ended    the run is over and the spot is back on the market
 * cancelled a checkout nobody finished, or a booking we refunded
 */
class SponsorSlot extends Model
{
    use HasFactory;

    public const PENDING = 'pending';

    public const PAID = 'paid';

    public const LIVE = 'live';

    public const ENDED = 'ended';

    public const CANCELLED = 'cancelled';

    /** Card tints, keyed by the value stored on the slot. */
    public const TINTS = [
        'blue' => 'border-[#a8c4ea] bg-[#e8f0fc]',
        'mint' => 'border-[#9ed4c3] bg-[#e6f5f0]',
        'lilac' => 'border-[#bdb1e8] bg-[#eee9fb]',
        'peach' => 'border-[#e8bd9c] bg-[#fdeee3]',
        'rose' => 'border-[#e8b1c1] bg-[#fceaf0]',
        'sand' => 'border-[#d5c9a4] bg-[#f6f2e4]',
    ];

    protected $fillable = [
        'name', 'tagline', 'url', 'logo_url', 'tint', 'side', 'position', 'sort_order',
        'is_active', 'starts_at', 'ends_at', 'status', 'paid_at', 'queued_at',
        'buyer_email', 'checkout_session_id', 'payment_reference', 'amount', 'currency',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'paid_at' => 'datetime',
            'queued_at' => 'datetime',
            'reminded_at' => 'datetime',
            'ending_notice_at' => 'datetime',
        ];
    }

    /** Cards that may be shown right now. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', self::LIVE)
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    /** Bookings that keep a spot off the market: the money is in. A checkout holds nothing. */
    public function scopeHoldingASpot(Builder $query): Builder
    {
        return $query->whereNotNull('position')->whereIn('status', [self::LIVE, self::PAID]);
    }

    /** Paid, but with no spot to go to yet. First paid, first served. */
    public function scopeQueued(Builder $query): Builder
    {
        return $query->where('status', self::PAID)->whereNull('position')->orderBy('paid_at');
    }

    /** Paid and holding a spot, but the buyer has not filled in their card yet. */
    public function scopeAwaitingDetails(Builder $query): Builder
    {
        return $query->where('status', self::PAID)->whereNotNull('position');
    }

    public function isLive(): bool
    {
        return $this->status === self::LIVE
            && $this->is_active
            && $this->name !== null
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isQueued(): bool
    {
        return $this->status === self::PAID && $this->position === null;
    }

    public function needsDetails(): bool
    {
        return $this->status === self::PAID && $this->position !== null;
    }

    /** Everything the card needs before it can go up. */
    public function hasCard(): bool
    {
        return filled($this->name) && filled($this->url) && filled($this->tagline);
    }

    /** Why a booking is not showing, for the admin table. */
    public function state(): string
    {
        return match (true) {
            $this->status === self::PENDING => 'Checkout',
            $this->isQueued() => 'In the queue',
            $this->needsDetails() => 'Awaiting details',
            $this->status === self::CANCELLED => 'Cancelled',
            $this->status === self::ENDED => 'Ended',
            ! $this->is_active => 'Paused',
            $this->starts_at !== null && $this->starts_at->isFuture() => 'Scheduled',
            $this->ends_at !== null && $this->ends_at->isPast() => 'Ended',
            default => 'Live',
        };
    }

    public function daysLeft(): ?int
    {
        return $this->ends_at ? max(0, (int) now()->diffInDays($this->ends_at, false)) : null;
    }

    public function tintClasses(): string
    {
        return self::TINTS[$this->tint] ?? self::TINTS['blue'];
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr((string) $this->name, 0, 2));
    }

    /**
     * Where the card points: the sponsor's own address, tagged so the visit shows up in their
     * analytics as ours. No redirect of ours in between.
     */
    public function linkUrl(): ?string
    {
        if (! $this->url) {
            return null;
        }

        return $this->url.(str_contains($this->url, '?') ? '&' : '?').http_build_query([
            'utm_source' => config('social.sponsors.utm_source'),
            'utm_medium' => 'referral',
            'utm_campaign' => 'sponsor_card',
        ]);
    }

    /** "left1": how a spot is named in a link. */
    public function spotKey(): ?string
    {
        return $this->position ? $this->side.$this->position : null;
    }

    public function freshToken(): string
    {
        return $this->token ??= Str::random(48);
    }

    public function cardUrl(): string
    {
        return route('sponsor.card', $this->freshToken());
    }
}
