<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use App\Enums\CreatorStatus;
use App\Enums\Platform;
use App\Enums\ProfileState;
use App\Social\Data\AccountProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'creator_category_id',
        'name',
        'slug',
        'bio',
        'avatar_url',
        'location',
        'website',
        'contact_email',
        'contact_enabled',
        'claimed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'metrics_synced_at' => 'datetime',
            'contact_enabled' => 'boolean',
            'has_verified_metrics' => 'boolean',
            'status' => CreatorStatus::class,
            'engagement_rate' => 'float',
            'average_view_percentage' => 'float',
            'posts_per_month' => 'float',
            'primary_platform' => Platform::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CreatorCategory::class, 'creator_category_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(CreatorSocialAccount::class)->orderBy('id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CreatorClaim::class);
    }

    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(CreatorPerformanceMetric::class);
    }

    public function contactRequests(): HasMany
    {
        return $this->hasMany(CreatorContactRequest::class)->latest();
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Creator::class, 'merged_into_creator_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CreatorStatus::Active);
    }

    public function scopeClaimed(Builder $query): Builder
    {
        return $query->whereNotNull('claimed_at');
    }

    public function scopeUnclaimed(Builder $query): Builder
    {
        return $query->whereNull('claimed_at');
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    /**
     * The derived public state of the profile.
     */
    public function profileState(): ProfileState
    {
        if (! $this->isClaimed()) {
            return ProfileState::Unclaimed;
        }

        $accounts = $this->relationLoaded('socialAccounts')
            ? $this->socialAccounts
            : $this->socialAccounts()->get();

        $statuses = $accounts->pluck('connection_status');

        if ($statuses->contains(ConnectionStatus::NeedsReconnection)) {
            return ProfileState::NeedsReconnection;
        }

        if (! $this->has_verified_metrics) {
            return ProfileState::Claimed;
        }

        $staleAfter = now()->subDays(config('social.sync.stale_after_days', 7));

        if ($this->metrics_synced_at === null || $this->metrics_synced_at->lt($staleAfter)) {
            return ProfileState::MetricsOutdated;
        }

        return ProfileState::VerifiedMetrics;
    }

    /**
     * The account whose metrics are surfaced on cards: a connected one where possible.
     */
    public function primaryAccount(): ?CreatorSocialAccount
    {
        $accounts = $this->relationLoaded('socialAccounts')
            ? $this->socialAccounts
            : $this->socialAccounts()->get();

        return $accounts->first(fn (CreatorSocialAccount $a) => $a->hasVerifiedMetrics())
            ?? $accounts->sortByDesc('follower_count')->first();
    }

    /**
     * Fill in profile fields the creator has not set themselves from a synced account profile.
     */
    public function fillMissingFrom(AccountProfile $profile): void
    {
        $this->avatar_url ??= $profile->avatarUrl;
        $this->bio ??= $profile->bio ? mb_substr($profile->bio, 0, 500) : null;
        $this->website ??= $profile->website;

        if ($this->isDirty()) {
            $this->save();
        }
    }

    public static function uniqueSlugFor(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'creator';
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->take(2)->map(fn ($p) => Str::upper(Str::substr($p, 0, 1)))->implode('');
    }
}
