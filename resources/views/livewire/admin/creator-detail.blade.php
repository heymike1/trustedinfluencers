<div>
    <x-admin-nav />
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <x-avatar :creator="$creator" size="lg" />
            <div>
                <h2 class="text-xl font-semibold text-ink-950 flex items-center gap-2">{{ $creator->name }} <x-state-badge :state="$state" /></h2>
                <p class="text-sm text-ink-500">
                    <a href="{{ route('creators.show', $creator) }}" class="hover:underline">/creators/{{ $creator->slug }}</a>
                    · {{ ucfirst($creator->status->value) }}
                    @if($creator->category) · {{ $creator->category->name }} @endif
                    · added {{ $creator->created_at->diffForHumans() }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" wire:click="toggleHidden" class="btn-secondary btn-sm">{{ $creator->status === \App\Enums\CreatorStatus::Hidden ? 'Restore' : 'Hide from marketplace' }}</button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <section class="card overflow-x-auto">
                <h3 class="px-4 py-3 border-b border-ink-100 text-sm font-semibold">Social accounts</h3>
                <table class="data-table">
                    <thead><tr><th>Platform</th><th>Handle</th><th>Provider ID</th><th>Status</th><th>Last synced</th><th></th></tr></thead>
                    <tbody>
                        @foreach($creator->socialAccounts as $account)
                            <tr wire:key="acc-{{ $account->id }}">
                                <td class="whitespace-nowrap"><span class="inline-flex items-center gap-1.5"><x-platform-icon :platform="$account->platform" class="size-3.5" /> {{ $account->platform->label() }}</span></td>
                                <td>{{ $account->handleWithAt() }} <span class="text-xs text-ink-400 tnum">{{ \App\Support\Format::compact($account->follower_count) }}</span></td>
                                <td class="font-mono text-xs text-ink-500">{{ $account->provider_account_id ?? '—' }}</td>
                                <td>
                                    <x-badge :variant="match($account->connection_status) { \App\Enums\ConnectionStatus::Connected => 'verified', \App\Enums\ConnectionStatus::SyncFailed, \App\Enums\ConnectionStatus::NeedsReconnection => 'warn', default => 'neutral' }">{{ $account->connection_status->label() }}</x-badge>
                                    @if($account->last_sync_error)<p class="mt-1 text-xs text-warn-700 max-w-xs">{{ $account->last_sync_error }}</p>@endif
                                </td>
                                <td class="text-xs text-ink-500 whitespace-nowrap">{{ $account->last_synced_at?->diffForHumans() ?? '—' }}</td>
                                <td class="text-right">
                                    @if($account->isConnected())
                                        <button type="button" wire:click="sync({{ $account->id }})" class="btn-secondary btn-sm" @if($account->isImporting()) disabled @endif>Sync</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="card overflow-x-auto">
                <h3 class="px-4 py-3 border-b border-ink-100 text-sm font-semibold">Claims</h3>
                <table class="data-table">
                    <thead><tr><th>User</th><th>Platform</th><th>Status</th><th>Expected ID</th><th>Returned</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse($creator->claims->sortByDesc('id') as $claim)
                            <tr>
                                <td class="text-xs">{{ $claim->user->email }}</td>
                                <td>{{ $claim->platform->label() }}</td>
                                <td>
                                    <x-badge :variant="match($claim->status) { \App\Enums\ClaimStatus::Verified => 'verified', \App\Enums\ClaimStatus::Failed => 'danger', default => 'neutral' }">{{ ucfirst($claim->status->value) }}</x-badge>
                                    @if($claim->failure_reason)<p class="text-xs text-ink-500 mt-1">{{ $claim->failure_reason }}</p>@endif
                                </td>
                                <td class="font-mono text-xs text-ink-500">{{ $claim->expected_provider_account_id ?? '(handle)' }}</td>
                                <td class="font-mono text-xs text-ink-500">{{ $claim->returned_provider_account_id ?? '—' }} @if($claim->returned_handle)<span class="font-sans">({{ "@".$claim->returned_handle }})</span>@endif</td>
                                <td class="text-xs text-ink-400 whitespace-nowrap">{{ $claim->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-ink-500">No claims.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="card overflow-x-auto">
                <h3 class="px-4 py-3 border-b border-ink-100 text-sm font-semibold">Contact requests <span class="text-ink-400 font-normal">({{ $creator->contactRequests->count() }})</span></h3>
                @if($creator->contactRequests->isEmpty())
                    <p class="px-4 py-3 text-sm text-ink-500">None.</p>
                @else
                    <table class="data-table">
                        <thead><tr><th>From</th><th>Subject</th><th>Delivered</th><th>When</th></tr></thead>
                        <tbody>
                            @foreach($creator->contactRequests->take(10) as $r)
                                <tr><td class="text-xs">{{ $r->name }} · {{ $r->email }}</td><td>{{ $r->subject }}</td><td class="text-xs">{{ $r->delivered_at ? 'Emailed' : 'Stored' }}</td><td class="text-xs text-ink-400 whitespace-nowrap">{{ $r->created_at->diffForHumans() }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>

        <aside class="space-y-4">
            <section class="card p-4 text-sm">
                <h3 class="font-semibold">Owner</h3>
                @if($creator->user)
                    <p class="mt-1 text-ink-700">{{ $creator->user->name }} <span class="text-ink-400">({{ $creator->user->email }})</span></p>
                    <p class="text-xs text-ink-400">Claimed {{ $creator->claimed_at?->diffForHumans() }}</p>
                    <button type="button" wire:click="releaseClaim" wire:confirm="Release this claim? The owner is unlinked, credentials are destroyed and verified data is deleted. Use this to resolve ownership disputes." class="btn-danger btn-sm mt-3">Release claim</button>
                @else
                    <p class="mt-1 text-ink-500">Unclaimed.</p>
                @endif
            </section>

            <section class="card p-4 text-sm">
                <h3 class="font-semibold">Merge into another profile</h3>
                <p class="mt-1 text-xs text-ink-500">This profile becomes a tombstone that redirects to the target. Accounts, claims and contact requests move across.</p>
                <form wire:submit="merge" class="mt-3 flex gap-2">
                    <input type="text" wire:model="mergeInto" placeholder="Target slug or ID" class="input">
                    <button type="submit" class="btn-secondary" wire:confirm="Merge {{ $creator->name }} into the target profile?">Merge</button>
                </form>
                <x-field-error for="mergeInto" />
            </section>

            @if($creator->status === \App\Enums\CreatorStatus::Merged && $creator->mergedInto)
                <section class="card p-4 text-sm">
                    <p>Merged into <a href="{{ route('admin.creators.show', $creator->mergedInto) }}" class="underline">{{ $creator->mergedInto->name }}</a>.</p>
                </section>
            @endif
        </aside>
    </div>
</div>
