<?php

namespace App\Jobs;

use App\Models\CreatorSocialAccount;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;

/**
 * Refreshes profile-level data (name, avatar, follower count) through the authenticated API.
 */
class SyncCreatorSocialProfile extends AccountSyncJob
{
    protected function sync(CreatorSocialAccount $account, SocialPlatformConnector $connector, AccountContext $context): void
    {
        $profile = $connector->syncProfile($context);

        $account->forceFill([
            'provider_account_id' => $profile->providerAccountId,
            'handle' => $profile->handle,
            'profile_url' => $account->platform->profileUrl($profile->handle),
            'display_name' => $profile->displayName ?? $account->display_name,
            'avatar_url' => $profile->avatarUrl ?? $account->avatar_url,
            'follower_count' => $profile->followerCount ?? $account->follower_count,
            'public_data' => array_filter([
                'bio' => $profile->bio,
                'website' => $profile->website,
            ] + $profile->metrics, fn ($v) => $v !== null),
            'public_synced_at' => now(),
        ])->save();

        $account->snapshots()->create([
            'captured_at' => now(),
            'metrics' => array_filter(['followers' => $profile->followerCount] + $profile->metrics, fn ($v) => $v !== null),
            'raw_provider_data' => $profile->raw,
        ]);

        $account->creator?->fillMissingFrom($profile);
    }
}
