<div>
    @if(! $creator)
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />

        @if($requests->isEmpty() && $filter === '')
            <div class="card mx-auto max-w-lg p-10 text-center">
                <span class="mx-auto flex size-11 items-center justify-center rounded-xl border border-ink-200 bg-ink-50 text-ink-500"><svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6.5l7 4.5 7-4.5"/><rect x="3" y="4.5" width="14" height="11" rx="2"/></svg></span>
                <p class="mt-4 font-semibold text-ink-950">Nothing yet</p>
                <p class="mt-1 text-sm text-ink-500">Messages sent through your public profile show up here.
                    @unless($creator->contact_enabled) Contact requests are switched off at the moment; turn them on under <a href="{{ route('account') }}" class="font-semibold text-brand-700">Profile</a>.@endunless
                </p>
            </div>
        @else
            <div class="grid gap-5 lg:grid-cols-[400px_minmax(0,1fr)] lg:items-start">
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3.5">
                        <p class="text-[15px] font-semibold text-ink-950">Inbox @if($unread)<span class="font-normal text-ink-500">· {{ $unread }} unread</span>@endif</p>
                        <div class="flex gap-1">
                            <button type="button" wire:click="$set('filter', '')" class="chip !py-1 !text-xs {{ $filter === '' ? 'chip-on' : '' }}">All</button>
                            <button type="button" wire:click="$set('filter', 'unread')" class="chip !py-1 !text-xs {{ $filter === 'unread' ? 'chip-on' : '' }}">Unread</button>
                        </div>
                    </div>
                    @forelse($requests as $request)
                        @php($isOpen = $open?->is($request))
                        <button type="button" wire:click="open({{ $request->id }})" wire:key="request-{{ $request->id }}" class="flex w-full items-start gap-3 border-t border-ink-100 px-4 py-3.5 text-left {{ $isOpen ? 'bg-verified-50' : 'hover:bg-ink-50' }}">
                            <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $request->read_at ? '' : 'bg-brand-600' }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex justify-between gap-2">
                                    <span class="truncate text-sm {{ $request->read_at ? 'font-medium' : 'font-semibold' }} text-ink-950">{{ $request->name }}@if($request->company) <span class="font-normal text-ink-500">· {{ $request->company }}</span>@endif</span>
                                    <span class="shrink-0 text-xs text-ink-400 tnum">{{ $request->created_at->diffForHumans(short: true, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}</span>
                                </span>
                                <span class="block truncate text-[13px] text-ink-500">{{ $request->subject }}</span>
                            </span>
                        </button>
                    @empty
                        <p class="border-t border-ink-100 px-4 py-8 text-center text-sm text-ink-500">No unread requests.</p>
                    @endforelse
                    @if($requests->hasPages())
                        <div class="border-t border-ink-100 bg-ink-50 px-4 py-2.5">{{ $requests->links() }}</div>
                    @endif
                </div>

                @if($open)
                    <div class="card p-7 space-y-5" wire:key="open-{{ $open->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="display text-[22px] tracking-[-0.02em]">{{ $open->subject }}</h2>
                                <p class="mt-1 text-[13.5px] text-ink-500">From <span class="font-semibold text-ink-950">{{ $open->name }}</span>@if($open->company) · {{ $open->company }}@endif · {{ $open->email }} · {{ $open->created_at->diffForHumans() }}</p>
                            </div>
                            @unless($open->read_at)<x-badge variant="verified">Unread</x-badge>@endunless
                        </div>
                        <div class="whitespace-pre-line rounded-xl border border-ink-100 bg-ink-50 p-5 text-[15px] leading-relaxed text-ink-900">{{ $open->message }}</div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <a href="mailto:{{ $open->email }}?subject={{ rawurlencode('Re: '.$open->subject) }}" class="btn-primary">Reply by email <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                            <button type="button" wire:click="markUnread({{ $open->id }})" class="btn-secondary">Mark as unread</button>
                            <span class="text-[13px] text-ink-500 sm:ml-auto">Replies go straight from your own mailbox. We never send on your behalf.</span>
                        </div>
                    </div>
                @else
                    <div class="card p-10 text-center text-sm text-ink-500">Pick a request to read it.</div>
                @endif
            </div>
        @endif
    @endif
</div>
