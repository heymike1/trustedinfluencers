<?php

namespace App\Social\Data;

/**
 * Normalised metrics for a single content item.
 *
 * Known keys (only present when the platform provides them):
 *  views, reach, impressions, likes, comments, shares, saves, replies, reposts, bookmarks,
 *  engagements, watch_time_seconds, average_watch_time_seconds, average_view_percentage,
 *  profile_clicks, url_clicks, profile_visits, follows, views_24h, views_7d
 */
final class ContentMetrics
{
    public function __construct(
        public readonly string $providerContentId,
        public readonly array $metrics,
        public readonly array $raw = [],
        /**
         * Time series that don't fit the flat map:
         *  retention   → 21 percentages, viewers still watching at 0%, 5%, … 100% of the item
         *  daily_views → views per day for the first 30 days after publishing
         */
        public readonly array $insights = [],
    ) {}
}
