<?php

namespace App\Jobs;

use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;
use App\Social\Exceptions\ReconnectionRequiredException;
use App\Social\OAuth\TokenManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Base for the sync chain. Handles token refresh, "needs reconnection" and "sync failed" states
 * so the concrete jobs only deal with data.
 */
abstract class AccountSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    public function __construct(public CreatorSocialAccount $account) {}

    public function handle(ConnectorManager $connectors, TokenManager $tokens): void
    {
        $account = $this->account->fresh();

        if ($account === null || ! $account->isConnected()) {
            $this->delete(); // Disconnected while queued: nothing to do and no error to record.

            return;
        }

        try {
            $context = $tokens->contextFor($account);
            $this->sync($account, $connectors->for($account->platform), $context);
        } catch (ReconnectionRequiredException $e) {
            $tokens->markNeedsReconnection($account, $e->getMessage());
            $this->fail($e);
        }
    }

    abstract protected function sync(CreatorSocialAccount $account, SocialPlatformConnector $connector, AccountContext $context): void;

    public function failed(Throwable $exception): void
    {
        $account = $this->account->fresh();

        if ($account === null || $account->connection_status === ConnectionStatus::NeedsReconnection) {
            return;
        }

        $account->forceFill([
            'connection_status' => ConnectionStatus::SyncFailed,
            'last_sync_error' => mb_substr($exception->getMessage(), 0, 1000),
        ])->save();
    }
}
