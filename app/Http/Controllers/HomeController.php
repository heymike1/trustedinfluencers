<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorClaim;
use App\Support\Format;

class HomeController extends Controller
{
    public function __invoke()
    {
        $recentlyVerified = Creator::query()
            ->active()
            ->claimed()
            ->with(['category', 'socialAccounts.performanceMetrics', 'socialAccounts.audience'])
            ->orderByDesc('claimed_at')
            ->limit(4)
            ->get();

        $categories = CreatorCategory::query()
            ->withCount([
                'creators' => fn ($q) => $q->active(),
                'creators as verified_count' => fn ($q) => $q->active()->where('has_verified_metrics', true),
            ])
            ->orderBy('sort_order')
            ->get();

        return view('home', [
            'recentlyVerified' => $recentlyVerified,
            'categories' => $categories,
            'counts' => [
                'creators' => Creator::active()->count(),
                'verified' => Creator::active()->where('has_verified_metrics', true)->count(),
            ],
            'pulse' => $this->pulse(),
        ]);
    }

    /**
     * Three moments in time for the strip under the leaderboard: what happened today, the strongest
     * profile that joined this week, and the all-time record for how much of a video gets watched.
     */
    private function pulse(): array
    {
        return [
            'refreshedToday' => Creator::active()->where('has_verified_metrics', true)->whereDate('metrics_synced_at', today())->count(),
            'claimedToday' => CreatorClaim::where('status', 'verified')->whereDate('verified_at', today())->count(),
            'newThisWeek' => Creator::active()
                ->where('has_verified_metrics', true)
                ->where('claimed_at', '>=', now()->subWeek())
                ->with('socialAccounts')
                ->orderByDesc('median_views')
                ->first(),
            'record' => $this->record(),
        ];
    }

    /**
     * The all-time tile. Watch time is the strongest signal, but only YouTube shares it, so
     * fall back to engagement when no connected platform reports it.
     *
     * @return array{creator: Creator, title: string, value: string, line: string}|null
     */
    private function record(): array
    {
        $watched = Creator::active()
            ->whereNotNull('average_view_percentage')
            ->with('socialAccounts')
            ->orderByDesc('average_view_percentage')
            ->first();

        if ($watched) {
            return [
                'creator' => $watched,
                'title' => 'most watched',
                'value' => Format::percent($watched->average_view_percentage, 0),
                'line' => 'of each video watched, on average',
            ];
        }

        $engaged = Creator::active()
            ->whereNotNull('engagement_rate')
            ->with('socialAccounts')
            ->orderByDesc('engagement_rate')
            ->first();

        return $engaged ? [
            'creator' => $engaged,
            'title' => 'most engaged',
            'value' => Format::percent($engaged->engagement_rate),
            'line' => 'engagement: likes, comments and saves per view',
        ] : null;
    }
}
