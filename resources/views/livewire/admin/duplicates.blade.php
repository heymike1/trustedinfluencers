<x-slot:heading>Duplicates</x-slot:heading>
<x-slot:subheading>Same name, or the same handle on more than one profile</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />
    <p class="text-sm text-ink-500 mb-4">Provider account IDs and normalised handles already prevent the same social account from being listed twice. These are the softer signals worth a look.</p>

    @if($groups->isEmpty())
        <div class="card p-8 text-center text-sm text-ink-500">No likely duplicates found.</div>
    @else
        <div class="space-y-4">
            @foreach($groups as $i => $group)
                <section class="card" wire:key="group-{{ $i }}">
                    <h3 class="px-4 py-3 border-b border-ink-100 text-sm font-semibold">{{ $group['reason'] }}</h3>
                    <table class="data-table">
                        <thead><tr><th>Creator</th><th>Accounts</th><th>Owner</th><th>Added</th><th class="text-right">Merge</th></tr></thead>
                        <tbody>
                            @foreach($group['creators'] as $creator)
                                <tr>
                                    <td><a href="{{ route('admin.creators.show', $creator) }}" class="font-medium hover:underline">{{ $creator->name }}</a> <span class="text-xs text-ink-400">/{{ $creator->slug }}</span></td>
                                    <td class="text-xs text-ink-500">
                                        @foreach($creator->socialAccounts as $a)
                                            <span class="inline-flex items-center gap-1 mr-2"><x-platform-icon :platform="$a->platform" class="size-3" /> {{ $a->handleWithAt() }} @if($a->isConnected())<x-badge variant="verified">connected</x-badge>@endif</span>
                                        @endforeach
                                    </td>
                                    <td class="text-xs text-ink-500">{{ $creator->user?->email ?? '—' }}</td>
                                    <td class="text-xs text-ink-400 whitespace-nowrap">{{ $creator->created_at->diffForHumans() }}</td>
                                    <td class="text-right whitespace-nowrap">
                                        @foreach($group['creators'] as $other)
                                            @if($other->id !== $creator->id)
                                                <button type="button" wire:click="merge({{ $creator->id }}, {{ $other->id }})" wire:confirm="Merge {{ $creator->name }} (#{{ $creator->id }}) into {{ $other->name }} (#{{ $other->id }})?" class="btn-secondary btn-sm">→ into #{{ $other->id }}</button>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach
        </div>
    @endif
</div>
