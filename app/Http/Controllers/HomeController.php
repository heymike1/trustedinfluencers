<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorClaim;

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
            'mostWatched' => Creator::active()
                ->whereNotNull('average_view_percentage')
                ->with('socialAccounts')
                ->orderByDesc('average_view_percentage')
                ->first(),
        ];
    }
}
