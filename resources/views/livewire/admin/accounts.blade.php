<x-slot:heading>Social accounts</x-slot:heading>
<x-slot:subheading>{{ number_format($accounts->total()) }} accounts across every profile</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-center gap-2 mb-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Handle or creator" class="input max-w-xs">
        <select wire:model.live="platform" class="input w-auto">
            <option value="">Every platform</option>
            @foreach($platforms as $p)<option value="{{ $p->value }}">{{ $p->label() }}</option>@endforeach
        </select>
        <select wire:model.live="status" class="input w-auto">
            <option value="">Every status</option>
            @foreach($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
        </select>
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th class="pl-4">Account</th><th>Creator</th><th>Status</th><th class="text-right">Audience</th><th class="text-right">Content</th><th>Last sync</th><th class="pr-4"></th></tr></thead>
            <tbody>
                @forelse($accounts as $account)
                    <tr wire:key="acc-{{ $account->id }}">
                        <td class="pl-4">
                            <p class="flex items-center gap-1.5 font-medium text-ink-950"><x-platform-icon :platform="$account->platform" class="size-3.5" :colored="true" /> {{ $account->handleWithAt() }}</p>
                            <p class="text-xs text-ink-500">{{ $account->platform->label() }}</p>
                        </td>
                        <td class="text-xs"><a href="{{ route('admin.creators.show', $account->creator) }}" class="font-medium text-brand-700 hover:underline">{{ $account->creator->name }}</a></td>
                        <td>
                            <x-badge :variant="$account->isConnected() ? 'verified' : ($account->connection_status === \App\Enums\ConnectionStatus::Unconnected ? 'neutral' : 'warn')">{{ $account->connection_status->label() }}</x-badge>
                            @if($account->last_sync_error)<p class="mt-1 max-w-[280px] text-xs text-warn-700">{{ \Illuminate\Support\Str::limit($account->last_sync_error, 90) }}</p>@endif
                        </td>
                        <td class="text-right tnum">{{ \App\Support\Format::compact($account->follower_count) }}</td>
                        <td class="text-right tnum"><a href="{{ route('admin.content', ['account' => $account->id]) }}" class="text-brand-700 hover:underline">{{ $account->contents_count }}</a></td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $account->last_synced_at?->diffForHumans() ?? '—' }}</td>
                        <td class="pr-4 text-right whitespace-nowrap">
                            @if($account->isConnected())
                                <button type="button" wire:click="sync({{ $account->id }})" class="btn-secondary btn-sm">Sync</button>
                                <button type="button" wire:click="disconnect({{ $account->id }})" wire:confirm="Disconnect {{ $account->handleWithAt() }}? Imported data is deleted." class="btn-secondary btn-sm">Disconnect</button>
                            @endif
                            <button type="button" wire:click="destroy({{ $account->id }})" wire:confirm="Remove {{ $account->handleWithAt() }} from {{ $account->creator->name }}?" class="btn-danger btn-sm">Remove</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-sm text-ink-500">No accounts match this.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $accounts->links() }}</div>
</div>
