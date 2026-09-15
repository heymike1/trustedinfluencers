<?php

namespace App\Actions\Sync;

use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use Illuminate\Support\Facades\DB;

/**
 * Disconnects an account and applies the deletion policy:
 *
 *  - credentials are destroyed immediately
 *  - verified data (content analytics, metric snapshots, performance rows) is deleted, because it was
 *    only ever available through the creator's own authenticated session
 *  - the public profile (name, handle, follower count as last seen) stays, exactly as it would if a
 *    visitor had added the creator
 */
class DisconnectAccount
{
    public function __construct(private readonly RefreshCreatorSummary $refreshSummary) {}

    public function handle(CreatorSocialAccount $account): void
    {
        DB::transaction(function () use ($account) {
            $account->snapshots()->delete();
            $account->performanceMetrics()->delete();
            $account->contents()->delete();

            $account->forgetTokens();
            $account->forceFill([
                'connection_status' => ConnectionStatus::Disconnected,
                'disconnected_at' => now(),
                'last_synced_at' => null,
                'last_sync_error' => null,
            ])->save();
        });

        $this->refreshSummary->handle($account->creator);
    }
}
