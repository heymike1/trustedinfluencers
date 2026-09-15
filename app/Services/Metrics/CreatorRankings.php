<?php

namespace App\Services\Metrics;

use App\Enums\Platform;
use App\Enums\RankMetric;
use App\Models\Creator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Positions a creator among verified creators on a given metric.
 */
class CreatorRankings
{
    /**
     * @return array{rank: int, total: int}|null null when the creator has no value for the metric
     */
    public function position(Creator $creator, RankMetric $metric, ?Platform $platform = null, ?int $categoryId = null): ?array
    {
        $value = $metric->value($creator);

        if ($value === null || ! $creator->has_verified_metrics) {
            return null;
        }

        $base = $this->ranked($metric, $platform, $categoryId);

        return [
            'rank' => (clone $base)->whereRaw('('.$metric->expression().') > ?', [$value])->count() + 1,
            'total' => (clone $base)->count(),
        ];
    }

    /**
     * Verified creators that have a value for the metric, best first.
     */
    public function ranked(RankMetric $metric, ?Platform $platform = null, ?int $categoryId = null): Builder
    {
        return Creator::query()
            ->active()
            ->where('has_verified_metrics', true)
            ->whereRaw('('.$metric->expression().') IS NOT NULL')
            ->when($platform, fn (Builder $q) => $q->where('primary_platform', $platform))
            ->when($categoryId, fn (Builder $q) => $q->where('creator_category_id', $categoryId))
            ->orderByRaw('('.$metric->expression().') DESC')
            ->orderByDesc('follower_count');
    }
}
