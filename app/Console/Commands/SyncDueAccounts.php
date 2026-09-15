<?php

namespace App\Console\Commands;

use App\Actions\Sync\StartAccountSync;
use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use Illuminate\Console\Command;

class SyncDueAccounts extends Command
{
    protected $signature = 'social:sync-due {--force : Ignore the refresh interval}';

    protected $description = 'Queue a metrics sync for every connected account whose data is due for a refresh';

    public function handle(StartAccountSync $startSync): int
    {
        $due = now()->subHours(config('social.sync.refresh_every_hours', 24));

        $accounts = CreatorSocialAccount::query()
            ->whereIn('connection_status', [ConnectionStatus::Connected, ConnectionStatus::SyncFailed])
            ->when(! $this->option('force'), fn ($q) => $q->where(fn ($q) => $q->whereNull('last_synced_at')->orWhere('last_synced_at', '<=', $due)))
            ->get();

        $accounts->each(fn (CreatorSocialAccount $account) => $startSync->handle($account));

        $this->info("Queued sync for {$accounts->count()} account(s).");

        return self::SUCCESS;
    }
}
