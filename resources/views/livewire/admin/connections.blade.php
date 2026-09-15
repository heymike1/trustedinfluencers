<div>
    <x-admin-nav />
    <x-notice :notice="$notice" />
    <div class="flex items-center gap-2 mb-4">
        <select wire:model.live="status" class="input w-auto">
            <option value="">All connected / attempted</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->label() }}</option>
            @endforeach
        </select>
        <p class="text-sm text-ink-500 ml-auto tnum">{{ number_format($accounts->total()) }} accounts</p>
    </div>
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Creator</th><th>Account</th><th>Status</th><th>Token expires</th><th>Last synced</th><th>Error</th><th></th></tr></thead>
            <tbody>
                @foreach($accounts as $account)
                    <tr wire:key="conn-{{ $account->id }}">
                        <td><a href="{{ route('admin.creators.show', $account->creator) }}" class="font-medium hover:underline">{{ $account->creator->name }}</a></td>
                        <td class="whitespace-nowrap"><span class="inline-flex items-center gap-1.5"><x-platform-icon :platform="$account->platform" class="size-3.5" /> {{ $account->handleWithAt() }}</span></td>
                        <td><x-badge :variant="match($account->connection_status) { \App\Enums\ConnectionStatus::Connected => 'verified', \App\Enums\ConnectionStatus::SyncFailed, \App\Enums\ConnectionStatus::NeedsReconnection => 'warn', default => 'neutral' }">{{ $account->connection_status->label() }}</x-badge></td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $account->token_expires_at?->diffForHumans() ?? '—' }}</td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $account->last_synced_at?->diffForHumans() ?? '—' }}</td>
                        <td class="text-xs text-ink-500 max-w-xs truncate" title="{{ $account->last_sync_error }}">{{ $account->last_sync_error ?? '—' }}</td>
                        <td class="text-right">
                            @if($account->isConnected())
                                <button type="button" wire:click="sync({{ $account->id }})" class="btn-secondary btn-sm" @if($account->isImporting()) disabled @endif>Sync</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $accounts->links() }}</div>
</div>
