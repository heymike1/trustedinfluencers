<?php

namespace App\Jobs;

use App\Actions\Sync\RefreshCreatorSummary;
use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use App\Services\Metrics\PerformanceCalculator;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;

/**
 * Final step of the chain: compute creator-level performance and mark the account as connected.
 */
class CalculateCreatorPerformance extends AccountSyncJob
{
    protected function sync(CreatorSocialAccount $account, SocialPlatformConnector $connector, AccountContext $context): void
    {
        app(PerformanceCalculator::class)->calculateAndStore($account);

        $account->forceFill([
            'connection_status' => ConnectionStatus::Connected,
            'last_synced_at' => now(),
            'last_sync_error' => null,
        ])->save();

        app(RefreshCreatorSummary::class)->handle($account->creator);
    }
}
