<x-slot:heading>Claims</x-slot:heading>
<x-slot:subheading>{{ number_format($claims->total()) }} attempts · a claim succeeds only when the signed-in account matches the profile</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-center gap-2 mb-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Creator or email" class="input max-w-xs">
        <select wire:model.live="status" class="input w-auto">
            <option value="">Every status</option>
            @foreach($statuses as $s)<option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>@endforeach
        </select>
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th class="pl-4">Creator</th><th>User</th><th>Platform</th><th>Status</th><th>What happened</th><th>When</th><th class="pr-4"></th></tr></thead>
            <tbody>
                @forelse($claims as $claim)
                    <tr wire:key="claim-{{ $claim->id }}">
                        <td class="pl-4 text-xs">
                            @if($claim->creator)<a href="{{ route('admin.creators.show', $claim->creator) }}" class="font-medium text-brand-700 hover:underline">{{ $claim->creator->name }}</a>@else<span class="text-ink-400">deleted</span>@endif
                        </td>
                        <td class="text-xs text-ink-500">{{ $claim->user?->email ?? '—' }}</td>
                        <td class="text-xs">{{ $claim->platform->label() }}</td>
                        <td><x-badge :variant="$claim->status === \App\Enums\ClaimStatus::Verified ? 'verified' : ($claim->status === \App\Enums\ClaimStatus::Failed ? 'danger' : 'neutral')">{{ ucfirst($claim->status->value) }}</x-badge></td>
                        <td class="max-w-[320px] text-xs text-ink-500">{{ $claim->failure_reason ?? ($claim->returned_handle ? 'Signed in as @'.$claim->returned_handle : '—') }}</td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $claim->created_at->diffForHumans() }}</td>
                        <td class="pr-4 text-right"><button type="button" wire:click="destroy({{ $claim->id }})" wire:confirm="Delete this claim record?" class="btn-danger btn-sm">Delete</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-sm text-ink-500">No claims match this.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $claims->links() }}</div>
</div>
