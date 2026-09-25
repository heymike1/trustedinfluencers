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
        'blue' => 'border-[#a8c4ea] bg-[#e8f0fc]',
        'mint' => 'border-[#9ed4c3] bg-[#e6f5f0]',
        'lilac' => 'border-[#bdb1e8] bg-[#eee9fb]',
        'peach' => 'border-[#e8bd9c] bg-[#fdeee3]',
        'rose' => 'border-[#e8b1c1] bg-[#fceaf0]',
        'sand' => 'border-[#d5c9a4] bg-[#f6f2e4]',
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
