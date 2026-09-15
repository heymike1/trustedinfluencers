<div>
    <x-admin-nav />
    <div class="flex items-center gap-2 mb-4">
        <select wire:model.live="status" class="input w-auto">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="failed">Failed</option>
        </select>
        <p class="text-sm text-ink-500 ml-auto tnum">{{ number_format($claims->total()) }} claims</p>
    </div>
    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Creator</th><th>User</th><th>Platform</th><th>Status</th><th>Expected</th><th>Returned</th><th>When</th></tr></thead>
            <tbody>
                @foreach($claims as $claim)
                    <tr wire:key="claim-{{ $claim->id }}">
                        <td><a href="{{ route('admin.creators.show', $claim->creator) }}" class="font-medium hover:underline">{{ $claim->creator->name }}</a></td>
                        <td class="text-xs text-ink-500">{{ $claim->user->email }}</td>
                        <td>{{ $claim->platform->label() }}</td>
                        <td>
                            <x-badge :variant="match($claim->status) { \App\Enums\ClaimStatus::Verified => 'verified', \App\Enums\ClaimStatus::Failed => 'danger', default => 'neutral' }">{{ ucfirst($claim->status->value) }}</x-badge>
                            @if($claim->failure_reason)<p class="text-xs text-ink-500 mt-1 max-w-xs">{{ $claim->failure_reason }}</p>@endif
                        </td>
                        <td class="font-mono text-xs text-ink-500">{{ $claim->expected_provider_account_id ?? '(handle)' }}</td>
                        <td class="font-mono text-xs text-ink-500">{{ $claim->returned_provider_account_id ?? '—' }}</td>
                        <td class="text-xs text-ink-400 whitespace-nowrap">{{ $claim->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $claims->links() }}</div>
</div>
