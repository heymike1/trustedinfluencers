<?php

namespace App\Services\Metrics;

use App\Enums\MetricWindow;
use App\Models\CreatorPerformanceMetric;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use Illuminate\Support\Collection;

/**
 * Turns per-item metrics into creator-level performance numbers, separately per content type
 * and calculation window. Median is computed everywhere a single viral item would distort the mean.
 */
class PerformanceCalculator
{
    /** Interaction keys that count towards engagement, whichever the platform provides. */
    private const INTERACTION_KEYS = ['likes', 'comments', 'shares', 'saves', 'replies', 'reposts', 'bookmarks'];

    /**
     * Calculate and persist all windows for the account. Returns the rows that were written.
     *
     * @return Collection<int, CreatorPerformanceMetric>
     */
    public function calculateAndStore(CreatorSocialAccount $account): Collection
    {
        $contents = $account->contents()
            ->whereNotNull('metrics')
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get();

        $written = collect();
        $now = now();

        foreach ($account->platform->contentTypes() as $type) {
            $ofType = $contents->where('content_type', $type)->values();

            foreach (MetricWindow::cases() as $window) {
                $sample = $this->select($ofType, $window);

                if ($sample->isEmpty()) {
                    CreatorPerformanceMetric::query()
                        ->where('creator_social_account_id', $account->id)
                        ->where('content_type', $type)
                        ->where('calculation_window', $window)
                        ->delete();

                    continue;
                }

                $values = $this->aggregate($sample);

                $written->push(CreatorPerformanceMetric::updateOrCreate(
                    [
                        'creator_social_account_id' => $account->id,
                        'content_type' => $type,
                        'calculation_window' => $window,
                    ],
                    $values + [
                        'creator_id' => $account->creator_id,
                        'platform' => $account->platform,
                        'sample_size' => $sample->count(),
                        'calculated_at' => $now,
                    ],
                ));
            }
        }

        return $written;
    }

    /**
     * Pure aggregation, kept separate so it can be unit-tested without the database.
     *
     * @param  Collection<int, SocialContent>  $sample
     * @return array<string, mixed>
     */
    public function aggregate(Collection $sample): array
    {
        $metrics = $sample->map(fn (SocialContent $c) => $c->metrics ?? []);

        $views = $metrics->pluck('views')->filter(fn ($v) => $v !== null)->all();
        $reach = $metrics->pluck('reach')->filter(fn ($v) => $v !== null)->all();
        $impressions = $metrics->pluck('impressions')->filter(fn ($v) => $v !== null)->all();

        $engagementRates = $metrics->map(function (array $m) {
            $denominator = $m['views'] ?? $m['impressions'] ?? $m['reach'] ?? null;

            if (! $denominator) {
                return null;
            }

            $interactions = array_sum(array_map(fn ($k) => $m[$k] ?? 0, self::INTERACTION_KEYS));

            return $interactions / $denominator * 100;
        })->filter(fn ($v) => $v !== null)->all();

        $extra = [];

        foreach (['likes', 'comments', 'shares', 'saves', 'replies', 'reposts', 'bookmarks', 'engagements', 'profile_clicks', 'url_clicks', 'watch_time_seconds', 'profile_visits', 'follows', 'views_24h', 'views_7d'] as $key) {
            $list = $metrics->pluck($key)->filter(fn ($v) => $v !== null)->all();

            if ($list !== []) {
                $extra["average_{$key}"] = round(Statistics::mean($list));
                $extra["median_{$key}"] = round(Statistics::median($list));
            }
        }

        if ($views !== []) {
            $extra['total_views'] = array_sum($views);
            $extra['min_views'] = min($views);
            $extra['max_views'] = max($views);

            // Reactions per 1,000 views, so channels of different sizes compare.
            foreach (['likes', 'comments', 'shares', 'saves', 'replies', 'reposts', 'bookmarks'] as $key) {
                $pairs = $metrics->filter(fn ($m) => isset($m['views'], $m[$key]) && $m['views'] > 0);
                if ($pairs->isNotEmpty()) {
                    $extra["{$key}_per_1k"] = round(Statistics::median($pairs->map(fn ($m) => $m[$key] / $m['views'] * 1000)->all()), 1);
                }
            }
        }

        if ($curve = $this->averageCurve($sample->map(fn (SocialContent $c) => $c->retentionCurve())->filter()->values())) {
            $extra['retention_curve'] = $curve;
        }

        if ($velocity = $this->velocityCurve($sample->map(fn (SocialContent $c) => $c->dailyViews())->filter()->values())) {
            $extra['velocity_curve'] = $velocity;
        }

        $extra += $this->cadence($sample);

        $roundOrNull = fn (?float $v, int $precision = 0) => $v === null ? null : round($v, $precision);

        return [
            'average_views' => $roundOrNull(Statistics::mean($views)),
            'median_views' => $roundOrNull(Statistics::median($views)),
            'average_reach' => $roundOrNull(Statistics::mean($reach)),
            'median_reach' => $roundOrNull(Statistics::median($reach)),
            'average_impressions' => $roundOrNull(Statistics::mean($impressions)),
            'median_impressions' => $roundOrNull(Statistics::median($impressions)),
            'engagement_rate' => $roundOrNull(Statistics::mean($engagementRates), 4),
            'average_watch_time' => $roundOrNull(Statistics::mean($metrics->pluck('average_watch_time_seconds')->filter(fn ($v) => $v !== null)->all()), 2),
            'average_view_percentage' => $roundOrNull(Statistics::mean($metrics->pluck('average_view_percentage')->filter(fn ($v) => $v !== null)->all()), 2),
            'extra' => $extra,
        ];
    }

    /**
     * Point-by-point average of retention curves (each 21 points, 0%–100% of the item).
     *
     * @param  Collection<int, array<int, float>>  $curves
     */
    public function averageCurve(Collection $curves): ?array
    {
        $curves = $curves->filter(fn ($c) => count($c) === 21)->values();

        if ($curves->isEmpty()) {
            return null;
        }

        return array_map(fn (int $i) => round($curves->avg(fn ($c) => $c[$i]), 1), range(0, 20));
    }

    /**
     * Average cumulative share of 30-day views reached on each day (30 points, ending at 100).
     * Only items that are at least 30 days old count, so young videos don't skew the shape.
     *
     * @param  Collection<int, array<int, int>>  $series
     */
    public function velocityCurve(Collection $series): ?array
    {
        $complete = $series->filter(fn ($s) => count($s) >= 30 && array_sum($s) > 0)->values();

        if ($complete->isEmpty()) {
            return null;
        }

        $shares = $complete->map(function (array $daily) {
            $total = array_sum(array_slice($daily, 0, 30));
            $running = 0;
            $out = [];
            foreach (array_slice($daily, 0, 30) as $views) {
                $running += $views;
                $out[] = $running / $total * 100;
            }

            return $out;
        });

        return array_map(fn (int $i) => round($shares->avg(fn ($s) => $s[$i]), 1), range(0, 29));
    }

    /**
     * How regularly the creator publishes, from the sample's publish dates.
     *
     * @param  Collection<int, SocialContent>  $sample
     * @return array{posts_per_month?: float, longest_gap_days?: int}
     */
    public function cadence(Collection $sample): array
    {
        $dates = $sample->pluck('published_at')->filter()->sortDesc()->values();

        if ($dates->count() < 2) {
            return [];
        }

        $spanDays = max(1, abs($dates->first()->diffInDays($dates->last())));
        $gaps = [];
        for ($i = 1; $i < $dates->count(); $i++) {
            $gaps[] = (int) round(abs($dates[$i - 1]->diffInDays($dates[$i])));
        }

        return [
            'posts_per_month' => round($dates->count() / ($spanDays / 30.4), 1),
            'longest_gap_days' => max($gaps),
        ];
    }

    /**
     * @param  Collection<int, SocialContent>  $contents  newest first
     * @return Collection<int, SocialContent>
     */
    public function select(Collection $contents, MetricWindow $window): Collection
    {
        if ($limit = $window->itemLimit()) {
            return $contents->take($limit)->values();
        }

        $since = now()->subDays($window->days());

        return $contents->filter(fn (SocialContent $c) => $c->published_at->gte($since))->values();
    }
}
