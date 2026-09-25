<x-slot:heading>Contact requests</x-slot:heading>
<x-slot:subheading>{{ number_format($requests->total()) }} messages · {{ $unread }} unread</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-center gap-2 mb-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Name, email or subject" class="input max-w-xs">
        <select wire:model.live="filter" class="input w-auto">
            <option value="">All messages</option>
            <option value="unread">Unread only</option>
        </select>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead><tr><th class="pl-4">From</th><th>To</th><th>Subject</th><th>When</th><th class="pr-4"></th></tr></thead>
            <tbody>
                @forelse($requests as $request)
                    <tr wire:key="req-{{ $request->id }}" class="{{ $request->read_at ? '' : 'bg-brand-50/40' }}">
                        <td class="pl-4">
                            <p class="font-medium text-ink-950">{{ $request->name }}@if($request->company) <span class="font-normal text-ink-500">· {{ $request->company }}</span>@endif</p>
                            <p class="text-xs text-ink-500">{{ $request->email }}</p>
                        </td>
                        <td class="text-xs">
                            @if($request->creator)<a href="{{ route('admin.creators.show', $request->creator) }}" class="text-brand-700 hover:underline">{{ $request->creator->name }}</a>@else<span class="text-ink-400">deleted</span>@endif
                        </td>
                        <td class="max-w-[280px] truncate text-xs">{{ $request->subject }}</td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $request->created_at->diffForHumans() }}</td>
                        <td class="pr-4 text-right whitespace-nowrap">
                            <button type="button" wire:click="show({{ $request->id }})" class="btn-secondary btn-sm">{{ $open === $request->id ? 'Hide' : 'Read' }}</button>
                            <button type="button" wire:click="toggleRead({{ $request->id }})" class="btn-secondary btn-sm">{{ $request->read_at ? 'Mark unread' : 'Mark read' }}</button>
                            <button type="button" wire:click="destroy({{ $request->id }})" wire:confirm="Delete this message?" class="btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                    @if($open === $request->id)
                        <tr wire:key="body-{{ $request->id }}"><td colspan="5" class="bg-ink-50 px-4 py-4">
                            <p class="whitespace-pre-line rounded-xl border border-ink-200 bg-white p-4 text-sm text-ink-900">{{ $request->message }}</p>
                            <a href="mailto:{{ $request->email }}?subject={{ rawurlencode('Re: '.$request->subject) }}" class="btn-secondary btn-sm mt-3">Reply by email</a>
                        </td></tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-sm text-ink-500">No messages match this.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</div>
