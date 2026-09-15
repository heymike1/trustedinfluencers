<?php

namespace App\Jobs;

use App\Actions\Sync\RefreshCreatorSummary;
use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Best-effort import of publicly available data for an account that nobody has claimed.
 * Never produces "verified" data; it only fills in public fields.
 */
class SyncPublicProfile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public CreatorSocialAccount $account) {}

    public function handle(ConnectorManager $connectors): void
    {
        $account = $this->account->fresh();

        if ($account === null || $account->isConnected()) {
            return;
        }

        $profile = $connectors->for($account->platform)->publicProfile($account->handle);

        if ($profile === null) {
            return;
        }

        // A duplicate listing may already hold this canonical id; leave that for admin merge.
        $idTaken = CreatorSocialAccount::where('platform', $account->platform)
            ->where('provider_account_id', $profile->providerAccountId)
            ->where('id', '!=', $account->id)
            ->exists();

        $account->forceFill([
            'provider_account_id' => $account->provider_account_id ?? ($idTaken ? null : $profile->providerAccountId),
            'handle' => $profile->handle,
            'profile_url' => $account->platform->profileUrl($profile->handle),
            'display_name' => $profile->displayName ?? $account->display_name,
            'avatar_url' => $profile->avatarUrl ?? $account->avatar_url,
            'follower_count' => $profile->followerCount ?? $account->follower_count,
            'public_data' => array_filter(['bio' => $profile->bio] + $profile->metrics, fn ($v) => $v !== null),
            'public_synced_at' => now(),
        ])->save();

        $account->creator?->fillMissingFrom($profile);

        app(RefreshCreatorSummary::class)->handle($account->creator);
    }
}
