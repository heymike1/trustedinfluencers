<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Enums\MetricWindow;
use App\Enums\Platform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorPerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'creator_social_account_id',
        'platform',
        'content_type',
        'calculation_window',
        'sample_size',
        'average_views',
        'median_views',
        'average_reach',
        'median_reach',
        'average_impressions',
        'median_impressions',
        'engagement_rate',
        'average_watch_time',
        'average_view_percentage',
        'extra',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'content_type' => ContentType::class,
            'calculation_window' => MetricWindow::class,
            'engagement_rate' => 'float',
            'average_watch_time' => 'float',
            'average_view_percentage' => 'float',
            'extra' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(CreatorSocialAccount::class, 'creator_social_account_id');
    }

    public function extra(string $key): int|float|array|null
    {
        return $this->extra[$key] ?? null;
    }
}
