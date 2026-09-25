<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A paid card in one of the two rails beside the page. Admin-managed; a slot shows only while
 * it is active and inside its booking window.
 */
class SponsorSlot extends Model
{
    use HasFactory;

    /** Card tints, keyed by the value stored on the slot. */
    public const TINTS = [
        'blue' => 'border-[#c9d9f0] bg-[#e8f0fc]',
        'mint' => 'border-[#c6e6db] bg-[#e6f5f0]',
        'lilac' => 'border-[#d9d2f2] bg-[#eee9fb]',
        'peach' => 'border-[#f3d9c6] bg-[#fdeee3]',
        'rose' => 'border-[#f2d0da] bg-[#fceaf0]',
        'sand' => 'border-[#e6dfc9] bg-[#f6f2e4]',
    ];

    protected $fillable = [
        'name', 'tagline', 'url', 'logo_url', 'tint', 'side', 'sort_order', 'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** Slots that may be shown right now. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function isLive(): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /** Why a slot is not showing, for the admin table. */
    public function state(): string
    {
        return match (true) {
            ! $this->is_active => 'Paused',
            $this->starts_at !== null && $this->starts_at->isFuture() => 'Scheduled',
            $this->ends_at !== null && $this->ends_at->isPast() => 'Ended',
            default => 'Live',
        };
    }

    public function tintClasses(): string
    {
        return self::TINTS[$this->tint] ?? self::TINTS['blue'];
    }

    public function initials(): string
    {
        return mb_strtoupper(mb_substr($this->name, 0, 2));
    }
}
