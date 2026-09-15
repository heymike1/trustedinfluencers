<div>
    <x-admin-nav />

    <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach($counts as $label => $value)
            <div class="card p-4">
                <dt class="text-xs text-ink-500">{{ $label }}</dt>
                <dd class="mt-1 text-2xl font-semibold tracking-tight tnum">{{ number_format($value) }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="card overflow-x-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b border-ink-100">
                <h2 class="text-sm font-semibold">Recent claims</h2>
                <a href="{{ route('admin.claims') }}" class="text-xs text-ink-500 hover:text-ink-950">All claims →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Creator</th><th>User</th><th>Status</th><th>When</th></tr></thead>
                <tbody>
                    @forelse($recentClaims as $claim)
                        <tr>
                            <td><a href="{{ route('admin.creators.show', $claim->creator) }}" class="font-medium hover:underline">{{ $claim->creator->name }}</a> <span class="text-ink-400 text-xs">{{ $claim->platform->label() }}</span></td>
                            <td class="text-ink-500">{{ $claim->user->email }}</td>
                            <td><x-badge :variant="match($claim->status) { \App\Enums\ClaimStatus::Verified => 'verified', \App\Enums\ClaimStatus::Failed => 'danger', default => 'neutral' }">{{ ucfirst($claim->status->value) }}</x-badge></td>
                            <td class="text-ink-400 text-xs whitespace-nowrap">{{ $claim->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-ink-500">No claims yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="card overflow-x-auto">
            <div class="flex items-center justify-between px-4 py-3 border-b border-ink-100">
                <h2 class="text-sm font-semibold">Failed syncs &amp; reconnections</h2>
                <a href="{{ route('admin.connections') }}" class="text-xs text-ink-500 hover:text-ink-950">All connections →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Account</th><th>Status</th><th>Error</th></tr></thead>
                <tbody>
                    @forelse($failedAccounts as $account)
                        <tr>
                            <td><a href="{{ route('admin.creators.show', $account->creator) }}" class="font-medium hover:underline">{{ $account->creator->name }}</a> <span class="text-ink-400 text-xs">{{ $account->platform->label() }} {{ $account->handleWithAt() }}</span></td>
                            <td><x-badge variant="warn">{{ $account->connection_status->label() }}</x-badge></td>
                            <td class="text-xs text-ink-500 max-w-xs truncate" title="{{ $account->last_sync_error }}">{{ $account->last_sync_error }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-ink-500">Everything is syncing fine.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</div>
