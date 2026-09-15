<?php

namespace App\Actions\Sync;

use App\Enums\MetricWindow;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;

/**
 * Rebuilds the denormalised columns on creators used for marketplace filtering and sorting.
 */
class RefreshCreatorSummary
{
    public function handle(Creator $creator): void
    {
        $creator->load(['socialAccounts.performanceMetrics']);

        $accounts = $creator->socialAccounts;
        $verified = $accounts->filter(fn (CreatorSocialAccount $a) => $a->hasVerifiedMetrics() && $a->performanceMetrics->isNotEmpty());

        $primary = $verified->sortByDesc('follower_count')->first();
        $performance = $primary?->performanceFor($primary->platform->primaryContentType(), MetricWindow::default());

        $creator->forceFill([
            'follower_count' => (int) $accounts->sum(fn (CreatorSocialAccount $a) => $a->follower_count ?? 0),
            'has_verified_metrics' => $verified->isNotEmpty(),
            'median_views' => $performance?->median_views,
            'average_views' => $performance?->average_views,
            'engagement_rate' => $performance?->engagement_rate,
            'average_view_percentage' => $performance?->average_view_percentage,
            'median_views_7d' => $performance?->extra('median_views_7d'),
            'posts_per_month' => $performance?->extra('posts_per_month'),
            'primary_platform' => $primary?->platform ?? $accounts->sortByDesc('follower_count')->first()?->platform,
            'metrics_synced_at' => $verified->max('last_synced_at'),
        ])->save();
    }
}
