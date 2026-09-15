<?php

namespace App\Models;

use App\Enums\ConnectionStatus;
use App\Enums\ContentType;
use App\Enums\MetricWindow;
use App\Enums\Platform;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class CreatorSocialAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'platform',
        'handle',
        'profile_url',
        'provider_account_id',
        'display_name',
        'avatar_url',
        'follower_count',
        'public_data',
        'public_synced_at',
        'connection_status',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scopes',
        'connected_at',
        'last_synced_at',
        'sync_requested_at',
        'last_sync_error',
        'disconnected_at',
    ];

    /** Tokens must never be serialised to the frontend. */
    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'connection_status' => ConnectionStatus::class,
            'public_data' => 'array',
            'scopes' => 'array',
            'public_synced_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'connected_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'sync_requested_at' => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    protected function accessToken(): Attribute
    {
        return $this->encryptedAttribute();
    }

    protected function refreshToken(): Attribute
    {
        return $this->encryptedAttribute();
    }

    private function encryptedAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Crypt::decryptString($value),
            set: fn (?string $value) => $value === null ? null : Crypt::encryptString($value),
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(SocialContent::class)->orderByDesc('published_at');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(CreatorMetricSnapshot::class);
    }

    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(CreatorPerformanceMetric::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CreatorClaim::class);
    }

    public function audience(): HasOne
    {
        return $this->hasOne(CreatorAudienceInsight::class);
    }

    public function isConnected(): bool
    {
        return $this->connection_status->hasCredentials();
    }

    /**
     * True once the creator authenticated and we have imported metrics through the API.
     * A later failed or in-progress sync keeps the previously imported metrics visible.
     */
    public function hasVerifiedMetrics(): bool
    {
        return $this->connection_status->hasCredentials() && $this->last_synced_at !== null;
    }

    public function isImporting(): bool
    {
        return in_array($this->connection_status, [ConnectionStatus::Connecting, ConnectionStatus::Importing], true);
    }

    public function tokenExpiresSoon(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->lt(now()->addMinutes(10));
    }

    /** Median views as a share of the audience, in percent. */
    public function viewsVsAudience(CreatorPerformanceMetric $performance): ?float
    {
        return $this->follower_count && $performance->median_views !== null ? $performance->median_views / $this->follower_count * 100 : null;
    }

    public function handleWithAt(): string
    {
        return '@'.$this->handle;
    }

    public function forgetTokens(): void
    {
        $this->forceFill([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'scopes' => null,
        ]);
    }

    /** Public-facing metrics for one content type in the default window. */
    public function performanceFor(ContentType $type, ?MetricWindow $window = null): ?CreatorPerformanceMetric
    {
        $window ??= MetricWindow::default();

        $metrics = $this->relationLoaded('performanceMetrics')
            ? $this->performanceMetrics
            : $this->performanceMetrics()->get();

        return $metrics->first(fn (CreatorPerformanceMetric $m) => $m->content_type === $type && $m->calculation_window === $window);
    }
}
