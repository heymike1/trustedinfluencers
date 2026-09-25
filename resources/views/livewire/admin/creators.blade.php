<x-slot:heading>Creators</x-slot:heading>
<x-slot:subheading>{{ number_format($creators->total()) }} profiles</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name, slug or handle" class="input max-w-xs">
        <select wire:model.live="filter" class="input w-auto">
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="claimed">Claimed</option>
            <option value="unclaimed">Unclaimed</option>
            <option value="hidden">Hidden</option>
            <option value="merged">Merged</option>
        </select>
        <p class="text-sm text-ink-500 ml-auto tnum">{{ number_format($creators->total()) }} results</p>
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th>Creator</th><th>Accounts</th><th>Status</th><th>Owner</th><th class="text-right">Followers</th><th class="text-right">Median views</th><th></th></tr></thead>
            <tbody>
                @foreach($creators as $creator)
                    <tr wire:key="creator-{{ $creator->id }}">
                        <td>
                            <a href="{{ route('admin.creators.show', $creator) }}" class="flex items-center gap-2 font-medium hover:underline">
                                <x-avatar :creator="$creator" size="xs" /> {{ $creator->name }}
                            </a>
                            <span class="text-xs text-ink-400">/{{ $creator->slug }}@if($creator->category) · {{ $creator->category->name }}@endif</span>
                        </td>
                        <td class="text-xs text-ink-500">
                            @foreach($creator->socialAccounts as $a)
                                <span class="inline-flex items-center gap-1 mr-2"><x-platform-icon :platform="$a->platform" class="size-3" /> {{ $a->handleWithAt() }}</span>
                            @endforeach
                        </td>
                        <td>
                            <x-badge :variant="$creator->status === \App\Enums\CreatorStatus::Active ? 'neutral' : 'warn'">{{ ucfirst($creator->status->value) }}</x-badge>
                            @if($creator->has_verified_metrics)<x-badge variant="verified">Verified</x-badge>@endif
                        </td>
                        <td class="text-xs text-ink-500">{{ $creator->user?->email ?? '—' }}</td>
                        <td class="text-right tnum">{{ \App\Support\Format::compact($creator->follower_count) }}</td>
                        <td class="text-right tnum">{{ \App\Support\Format::compact($creator->median_views) }}</td>
                        <td class="text-right whitespace-nowrap">
                            @if($creator->status === \App\Enums\CreatorStatus::Active)
                                <button type="button" wire:click="hide({{ $creator->id }})" class="btn-secondary btn-sm">Hide</button>
                            @elseif($creator->status === \App\Enums\CreatorStatus::Hidden)
                                <button type="button" wire:click="restore({{ $creator->id }})" class="btn-secondary btn-sm">Restore</button>
                            @endif
                            @unless($creator->isClaimed())
                                <button type="button" wire:click="destroy({{ $creator->id }})" wire:confirm="Delete {{ $creator->name }} permanently?" class="btn-danger btn-sm">Delete</button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $creators->links() }}</div>
</div>
