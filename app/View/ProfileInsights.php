<?php

namespace App\View;

use App\Enums\ContentType;
use App\Models\CreatorAudienceInsight;
use App\Models\CreatorPerformanceMetric;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Support\Countries;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Everything a profile page shows for one account, one content type and one window.
 * Derived numbers live here so Blade only formats.
 */
class ProfileInsights
{
    /**
     * @param  Collection<int, SocialContent>  $contents  newest first
     */
    public function __construct(
        public readonly CreatorSocialAccount $account,
        public readonly CreatorPerformanceMetric $performance,
        public readonly ContentType $type,
        public readonly Collection $contents,
        public readonly ?CreatorAudienceInsight $audience,
    ) {}

    /** Median views as a share of the audience size, in percent. */
    public function viewsVsAudience(): ?float
    {
        return $this->account->viewsVsAudience($this->performance);
    }

    public function averageLength(): ?float
    {
        return $this->contents->avg('duration_seconds') ?: null;
    }

    /** Average watch time as a share of the average item length, in percent. */
    public function watchShare(): ?float
    {
        $length = $this->averageLength();

        return $length && $this->performance->average_watch_time ? $this->performance->average_watch_time / $length * 100 : null;
    }

    /** Median saves per person reached, in percent (Instagram). */
    public function saveRate(): ?float
    {
        $saves = $this->performance->extra('median_saves');

        return $this->performance->median_reach && $saves !== null ? $saves / $this->performance->median_reach * 100 : null;
    }

    /** Median link clicks per view, in percent (X). */
    public function linkClickRate(): ?float
    {
        $clicks = $this->performance->extra('median_url_clicks');

        return $this->performance->median_views && $clicks !== null ? $clicks / $this->performance->median_views * 100 : null;
    }

    /** @return array<int, float>|null 21 points, 0%–100% of the item */
    public function retentionCurve(): ?array
    {
        return $this->performance->extra('retention_curve');
    }

    /** @return array<int, float>|null 30 points, cumulative share of first-month views */
    public function velocityCurve(): ?array
    {
        return $this->performance->extra('velocity_curve');
    }

    public function nonFollowerShare(): ?float
    {
        return $this->audience?->follower_type['non_follower'] ?? null;
    }

    public function followerShare(): ?float
    {
        return $this->audience?->follower_type['follower'] ?? null;
    }

    /** One metric per item, oldest first, for bar strips. */
    public function series(string $metric): array
    {
        return $this->contents->reverse()->map(fn (SocialContent $c) => $c->metric($metric) ?? 0)->values()->all();
    }

    /** Reach per item split into the follower part, oldest first. Null when Instagram gave no split. */
    public function followerReachSeries(): ?array
    {
        $share = $this->followerShare();

        return $share === null ? null : array_map(fn ($reach) => (int) round($reach * $share / 100), $this->series('reach'));
    }

    /** Watch time as a share of each item's length, oldest first. */
    public function watchShareSeries(): array
    {
        return $this->contents->reverse()->map(function (SocialContent $c) {
            $watch = $c->metric('average_watch_time_seconds');

            return $c->duration_seconds && $watch ? round($watch / $c->duration_seconds * 100, 1) : 0;
        })->values()->all();
    }

    /** Items published per calendar month across everything imported for the account: 'Y-m' => count. */
    public function cadenceByMonth(): Collection
    {
        return $this->account->contents()
            ->whereNotNull('published_at')
            ->pluck('published_at')
            ->groupBy(fn ($date) => $date->format('Y-m'))
            ->map->count()
            ->sortKeys();
    }

    public function topCountries(int $limit = 5): array
    {
        $rows = $this->audience?->top('countries', $limit) ?? [];
        $out = [];
        foreach ($rows as $code => $pct) {
            $out[Countries::NAMES[$code] ?? $code] = $pct;
        }

        return $out;
    }

    /** "71% men · 29% women" */
    public function genderSummary(): ?string
    {
        $rows = $this->audience?->top('gender', 2);

        return $rows ? collect($rows)->map(fn ($v, $k) => round($v).'% '.match ($k) {
            'male' => 'men', 'female' => 'women', default => $k
        })->join(' · ') : null;
    }

    public function deviceSummary(): ?string
    {
        $rows = $this->audience?->top('devices', 3);

        return $rows ? collect($rows)->map(fn ($v, $k) => round($v).'% '.$k)->join(' · ') : null;
    }

    public function citySummary(): ?string
    {
        $rows = $this->audience?->top('cities', 4);

        return $rows ? collect($rows)->map(fn ($v, $k) => Str::before($k, ',').' '.round($v).'%')->join(' · ') : null;
    }

    /** The one viral item whose views dwarf the median, if any. */
    public function viralContentId(): ?int
    {
        $top = $this->contents->sortByDesc(fn (SocialContent $c) => $c->metric('views'))->first();
        $median = $this->performance->median_views;

        return $top && $median && ($top->metric('views') ?? 0) > $median * 3 ? $top->id : null;
    }
}
