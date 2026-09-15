<div>
    @if(! $creator)
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />

        @if($requests->isEmpty())
            <div class="card p-8 text-center text-sm text-ink-500">Nothing yet. Messages sent through your public profile show up here{{ $creator->contact_enabled ? '' : ' (you have contact requests switched off at the moment)' }}.</div>
        @else
            <div class="space-y-3">
                @foreach($requests as $request)
                    <details class="card group" wire:key="request-{{ $request->id }}" @if(! $request->read_at) x-data @toggle="if ($el.open) $wire.markRead({{ $request->id }})" @endif>
                        <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2 px-4 py-3 list-none">
                            <div class="flex items-center gap-3 min-w-0">
                                @if(! $request->read_at)<span class="size-2 rounded-full bg-brand-600 shrink-0"></span>@endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-ink-950 truncate">{{ $request->subject }}</p>
                                    <p class="text-xs text-ink-500 truncate">{{ $request->name }}@if($request->company) · {{ $request->company }}@endif · {{ $request->email }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-ink-400">{{ $request->created_at->diffForHumans() }}</p>
                        </summary>
                        <div class="border-t border-ink-100 px-4 py-3 text-sm text-ink-700 whitespace-pre-line">{{ $request->message }}</div>
                        <div class="border-t border-ink-100 px-4 py-2 text-xs">
                            <a href="mailto:{{ $request->email }}?subject={{ rawurlencode('Re: '.$request->subject) }}" class="text-ink-900 underline underline-offset-2">Reply by email</a>
                        </div>
                    </details>
                @endforeach
            </div>
            <div class="mt-4">{{ $requests->links() }}</div>
        @endif
    @endif
</div>
