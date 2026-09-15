<?php

namespace App\Actions\Admin;

use App\Actions\Sync\RefreshCreatorSummary;
use App\Enums\CreatorStatus;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Folds a duplicate profile into the canonical one. The duplicate stays as a tombstone that
 * redirects to the target so old links keep working.
 */
class MergeCreators
{
    public function __construct(private readonly RefreshCreatorSummary $refreshSummary) {}

    public function handle(Creator $duplicate, Creator $target): Creator
    {
        if ($duplicate->id === $target->id) {
            throw ValidationException::withMessages(['merge' => 'Pick two different profiles.']);
        }

        if ($duplicate->isClaimed() && $target->isClaimed() && $duplicate->user_id !== $target->user_id) {
            throw ValidationException::withMessages(['merge' => 'Both profiles are claimed by different users. Resolve the ownership dispute first.']);
        }

        DB::transaction(function () use ($duplicate, $target) {
            $duplicate->socialAccounts->each(function (CreatorSocialAccount $account) use ($target) {
                $conflict = $target->socialAccounts()->where('platform', $account->platform)->first();

                if ($conflict === null) {
                    $account->update(['creator_id' => $target->id]);

                    return;
                }

                // Same platform on both sides: keep the connected one, otherwise the target's.
                if ($account->isConnected() && ! $conflict->isConnected()) {
                    $conflict->delete();
                    $account->update(['creator_id' => $target->id]);
                } else {
                    $account->delete();
                }
            });

            $duplicate->contactRequests()->update(['creator_id' => $target->id]);
            $duplicate->claims()->update(['creator_id' => $target->id]);
            $duplicate->performanceMetrics()->update(['creator_id' => $target->id]);

            $target->fill([
                'bio' => $target->bio ?? $duplicate->bio,
                'avatar_url' => $target->avatar_url ?? $duplicate->avatar_url,
                'website' => $target->website ?? $duplicate->website,
                'location' => $target->location ?? $duplicate->location,
                'creator_category_id' => $target->creator_category_id ?? $duplicate->creator_category_id,
            ]);

            if ($duplicate->isClaimed() && ! $target->isClaimed()) {
                $target->forceFill(['user_id' => $duplicate->user_id, 'claimed_at' => $duplicate->claimed_at]);
            }

            $target->save();

            $duplicate->forceFill([
                'user_id' => null,
                'status' => CreatorStatus::Merged,
                'merged_into_creator_id' => $target->id,
            ])->save();
        });

        $this->refreshSummary->handle($target->fresh());

        return $target;
    }
}
