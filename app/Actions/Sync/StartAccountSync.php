<?php

namespace App\Actions\Sync;

use App\Enums\ConnectionStatus;
use App\Jobs\CalculateCreatorPerformance;
use App\Jobs\SyncCreatorContent;
use App\Jobs\SyncCreatorMetrics;
use App\Jobs\SyncCreatorSocialProfile;
use App\Models\CreatorSocialAccount;
use Illuminate\Support\Facades\Bus;

/**
 * Queues the full sync chain for a connected account. Cheap to call: the OAuth callback stays fast.
 */
class StartAccountSync
{
    public function handle(CreatorSocialAccount $account): void
    {
        $account->forceFill([
            'connection_status' => ConnectionStatus::Importing,
            'sync_requested_at' => now(),
            'last_sync_error' => null,
        ])->save();

        Bus::chain([
            new SyncCreatorSocialProfile($account),
            new SyncCreatorContent($account),
            new SyncCreatorMetrics($account),
            new CalculateCreatorPerformance($account),
        ])->dispatch();
    }
}
