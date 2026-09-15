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
                'claimedThisWeek' => CreatorClaim::where('status', 'verified')->where('verified_at', '>=', now()->subWeek())->count(),
            ],
        ]);
    }
}
