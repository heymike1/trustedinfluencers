<?php

namespace App\Http\Controllers;

use App\Enums\CreatorStatus;
use App\Enums\Platform;
use App\Enums\RankMetric;
use App\Models\Creator;
use App\Services\Metrics\CreatorRankings;
use Illuminate\Http\Request;

class CreatorController extends Controller
{
    public function show(Request $request, Creator $creator, CreatorRankings $rankings)
    {
        // Merged duplicates keep their URL but redirect to the canonical profile.
        if ($creator->status === CreatorStatus::Merged && $creator->mergedInto) {
            return redirect()->route('creators.show', $creator->mergedInto, 301);
        }

        // An unlisted profile is still reachable for its owner, so they can see what they hid.
        abort_unless($creator->isPublic() || ($creator->status === CreatorStatus::Active && $creator->isOwnedBy($request->user())), 404);

        $creator->load(['category', 'socialAccounts']);

        return view('creators.show', [
            'creator' => $creator,
            'ranks' => $this->ranks($creator, $rankings),
        ]);
    }

    /**
     * Two positions worth showing on the band: overall by median views, and the platform's
     * headline quality metric within the creator's category.
     */
    private function ranks(Creator $creator, CreatorRankings $rankings): array
    {
        if (! $creator->has_verified_metrics) {
            return [];
        }

        $ranks = [];

        if ($overall = $rankings->position($creator, RankMetric::MedianViews)) {
            $ranks[] = ['title' => 'Top performers', 'rank' => $overall['rank'], 'hint' => "of {$overall['total']} verified creators by median views"];
        }

        $quality = $creator->primary_platform === Platform::YouTube ? RankMetric::Watched : RankMetric::Engagement;
        $scope = $creator->category ? "in {$creator->category->name}" : 'overall';

        if ($second = $rankings->position($creator, $quality, null, $creator->creator_category_id)) {
            $ranks[] = ['title' => $quality === RankMetric::Watched ? 'Most watched' : 'Most engaged', 'rank' => $second['rank'], 'hint' => ($quality === RankMetric::Watched ? 'most watched' : 'most engaged')." of {$second['total']} {$scope}"];
        }

        return $ranks;
    }
}
