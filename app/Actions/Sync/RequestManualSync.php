<?php

namespace App\Actions\Sync;

use App\Models\CreatorSocialAccount;
use Illuminate\Validation\ValidationException;

/**
 * "Sync now" from the creator dashboard, rate limited per account.
 */
class RequestManualSync
{
    public function __construct(private readonly StartAccountSync $startSync) {}

    public function handle(CreatorSocialAccount $account): void
    {
        if (! $account->isConnected()) {
            throw ValidationException::withMessages(['sync' => 'Connect the account first.']);
        }

        if ($account->isImporting()) {
            throw ValidationException::withMessages(['sync' => 'A sync is already running. Give it a minute.']);
        }

        $cooldown = config('social.sync.manual_cooldown_minutes', 60);

        if ($account->sync_requested_at && $account->sync_requested_at->gt(now()->subMinutes($cooldown))) {
            $next = $account->sync_requested_at->addMinutes($cooldown);

            throw ValidationException::withMessages(['sync' => 'You can sync again '.$next->diffForHumans().'.']);
        }

        $this->startSync->handle($account);
    }
}
