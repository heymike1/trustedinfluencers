<?php

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_social_account_id',
        'provider_content_id',
        'content_type',
        'title',
        'url',
        'thumbnail_url',
        'duration_seconds',
        'published_at',
        'metrics',
        'insights',
        'metrics_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'content_type' => ContentType::class,
            'published_at' => 'datetime',
            'metrics' => 'array',
            'insights' => 'array',
            'metrics_synced_at' => 'datetime',
        ];
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(CreatorSocialAccount::class, 'creator_social_account_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(CreatorMetricSnapshot::class);
    }

    public function metric(string $key): int|float|null
    {
        return $this->metrics[$key] ?? null;
    }

    /** @return array<int, float>|null retention curve as percentages, 0% → 100% of the video */
    public function retentionCurve(): ?array
    {
        return $this->insights['retention'] ?? null;
    }

    /** @return array<int, int>|null views per day for the first 30 days */
    public function dailyViews(): ?array
    {
        return $this->insights['daily_views'] ?? null;
    }
}
