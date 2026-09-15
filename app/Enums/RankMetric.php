<?php

namespace App\Enums;

use App\Models\Creator;
use App\Support\Format;

/**
 * The creator-level numbers people can rank and sort by. Each maps to a summary column on
 * creators (rebuilt after every sync) so ranking is a plain ORDER BY.
 */
enum RankMetric: string
{
    case MedianViews = 'median_views';
    case Watched = 'watched';
    case ViewsVsAudience = 'views_vs_audience';
    case Engagement = 'engagement';
    case Views7d = 'views_7d';
    case Cadence = 'cadence';

    public function label(): string
    {
        return match ($this) {
            self::MedianViews => 'Median views',
            self::Watched => 'How much gets watched',
            self::ViewsVsAudience => 'Views vs. audience',
            self::Engagement => 'Engagement',
            self::Views7d => 'Views in the first week',
            self::Cadence => 'Posts regularly',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::MedianViews => 'Median views',
            self::Watched => 'Watched',
            self::ViewsVsAudience => 'Views vs. audience',
            self::Engagement => 'Engagement',
            self::Views7d => 'First-week views',
            self::Cadence => 'Posts / month',
        };
    }

    /** SQL expression on the creators table used for ordering and ranking. */
    public function expression(): string
    {
        return match ($this) {
            self::MedianViews => 'median_views',
            self::Watched => 'average_view_percentage',
            self::ViewsVsAudience => 'CASE WHEN follower_count > 0 THEN median_views * 1.0 / follower_count END',
            self::Engagement => 'engagement_rate',
            self::Views7d => 'median_views_7d',
            self::Cadence => 'posts_per_month',
        };
    }

    public function value(Creator $creator): int|float|null
    {
        return match ($this) {
            self::MedianViews => $creator->median_views,
            self::Watched => $creator->average_view_percentage,
            self::ViewsVsAudience => $creator->follower_count > 0 && $creator->median_views !== null ? $creator->median_views / $creator->follower_count : null,
            self::Engagement => $creator->engagement_rate,
            self::Views7d => $creator->median_views_7d,
            self::Cadence => $creator->posts_per_month,
        };
    }

    public function format(int|float|null $value): string
    {
        return match ($this) {
            self::MedianViews, self::Views7d => Format::compact($value),
            self::Watched => Format::percent($value, 0),
            self::ViewsVsAudience => $value === null ? '—' : Format::percent($value * 100, 0),
            self::Engagement => Format::percent($value),
            self::Cadence => $value === null ? '—' : number_format($value, 1),
        };
    }

    /** Only YouTube reports how much of a video gets watched. */
    public function availableFor(?Platform $platform): bool
    {
        return match ($this) {
            self::Watched, self::Views7d => $platform === null || $platform === Platform::YouTube,
            default => true,
        };
    }

    /** One-line explanation shown under the ranking. */
    public function explanation(): string
    {
        return match ($this) {
            self::MedianViews => 'Median views is what a typical piece of content gets. One viral hit doesn’t drag it up.',
            self::Watched => '“Watched” is how much of a video people watch on average. YouTube only.',
            self::ViewsVsAudience => 'Median views as a share of subscribers or followers. Shows how much of the audience actually turns up.',
            self::Engagement => 'Likes, comments, shares and saves per view, averaged per post.',
            self::Views7d => 'Median views a video gets in its first seven days. YouTube only.',
            self::Cadence => 'How many pieces of content go out per month, from the recent uploads.',
        };
    }
}
