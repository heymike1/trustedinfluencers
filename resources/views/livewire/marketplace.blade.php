<div>
    <x-page-band>
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <h1 class="display text-3xl sm:text-[40px] leading-[1.05]">Browse creators</h1>
                <p class="mt-2 text-[15px] text-ink-700 tnum">{{ number_format($total) }} creators · <span class="font-semibold text-brand-700">{{ number_format($verifiedTotal) }} with verified numbers</span></p>
            </div>
            <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                <div class="relative w-full sm:w-auto">
                    <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 size-4 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name, handle or category" class="input w-full sm:w-80 rounded-full border-band-edge py-2.5 pl-11 pr-4">
                </div>
                <a href="{{ route('creators.create') }}" class="btn-secondary border-band-edge">Add a creator</a>
            </div>
        </div>
    </x-page-band>

    {{-- Platform tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex gap-1.5 overflow-x-auto">
            <button type="button" wire:click="$set('platform', '')" class="chip !py-2 {{ $platform === '' ? 'chip-on' : '' }}">All platforms</button>
            @foreach($platforms as $p)
                <button type="button" wire:click="$set('platform', '{{ $p->value }}')" class="chip !py-2 {{ $platform === $p->value ? 'chip-on' : '' }}">
                    <x-platform-icon :platform="$p" class="size-3.5" :colored="$platform !== $p->value" /> {{ $p->label() }} <span class="tnum {{ $platform === $p->value ? 'opacity-70' : 'text-ink-400' }}">{{ $platformCounts[$p->value] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
        <p class="text-xs text-ink-500">Columns change with the platform, because what matters for a campaign differs on each.</p>
    </div>

    {{-- Filter bar --}}
    <div class="flex flex-wrap items-center gap-2 mb-3" x-data="{ more: {{ $followersMin !== '' || $followersMax !== '' || $medianViewsMin !== '' || $averageViewsMin !== '' || $engagementMin !== '' ? 'true' : 'false' }} }">
        <select wire:model.live="category" class="input w-auto rounded-full py-1.5 pl-3 pr-8">
            <option value="">Any category</option>
            @foreach($categories as $c)
                <option value="{{ $c->slug }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="claimed" class="input w-auto rounded-full py-1.5 pl-3 pr-8">
            <option value="">Claimed and unclaimed</option>
            <option value="claimed">Claimed only</option>
            <option value="unclaimed">Unclaimed only</option>
        </select>
        <label class="chip cursor-pointer {{ $verified ? 'chip-on' : '' }}"><input type="checkbox" wire:model.live="verified" class="sr-only">
            <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified only
        </label>
        <button type="button" @click="more = !more" class="chip" :class="more && 'border-brand-700 text-brand-700'">Numbers <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg></button>
        @if($this->hasActiveFilters())
            <button type="button" wire:click="resetFilters" class="text-xs text-ink-500 underline underline-offset-2 hover:text-ink-950">Clear</button>
        @endif
        <div class="ml-auto flex items-center gap-2 text-xs text-ink-500">
            Sort
            <select wire:model.live="sort" class="input w-auto rounded-full py-1.5 pl-3 pr-8 text-xs">
                <option value="followers">Most {{ $activePlatform === \App\Enums\Platform::YouTube ? 'subscribers' : 'followers' }}</option>
                <option value="median_views">Highest median views</option>
                @if($activePlatform === \App\Enums\Platform::YouTube || $activePlatform === null)<option value="watched">Most watched</option>@endif
                <option value="engagement">Highest engagement</option>
                <option value="newest">Recently added</option>
            </select>
            <div class="flex rounded-full border border-ink-200 bg-white p-0.5">
                <button type="button" wire:click="$set('view', 'table')" class="rounded-full px-2.5 py-1 {{ $view === 'table' ? 'bg-brand-700 text-white' : 'text-ink-600' }}">Table</button>
                <button type="button" wire:click="$set('view', 'cards')" class="rounded-full px-2.5 py-1 {{ $view === 'cards' ? 'bg-brand-700 text-white' : 'text-ink-600' }}">Cards</button>
            </div>
        </div>

        <div x-show="more" x-cloak class="w-full grid gap-3 rounded-2xl border border-ink-200 bg-white p-4 sm:grid-cols-5">
            <div>
                <label class="text-xs text-ink-500" for="followersMin">{{ $activePlatform === \App\Enums\Platform::YouTube ? 'Subscribers' : 'Followers' }} at least</label>
                <input id="followersMin" type="number" min="0" step="1000" wire:model.live.debounce.400ms="followersMin" placeholder="e.g. 10000" class="input mt-1 py-1.5">
            </div>
            <div>
                <label class="text-xs text-ink-500" for="followersMax">{{ $activePlatform === \App\Enums\Platform::YouTube ? 'Subscribers' : 'Followers' }} at most</label>
                <input id="followersMax" type="number" min="0" step="1000" wire:model.live.debounce.400ms="followersMax" placeholder="e.g. 500000" class="input mt-1 py-1.5">
            </div>
            <div>
                <label class="text-xs text-ink-500" for="medianViewsMin">Median views at least</label>
                <input id="medianViewsMin" type="number" min="0" step="1000" wire:model.live.debounce.400ms="medianViewsMin" placeholder="e.g. 50000" class="input mt-1 py-1.5">
            </div>
            <div>
                <label class="text-xs text-ink-500" for="averageViewsMin">Average views at least</label>
                <input id="averageViewsMin" type="number" min="0" step="1000" wire:model.live.debounce.400ms="averageViewsMin" placeholder="e.g. 50000" class="input mt-1 py-1.5">
            </div>
            <div>
                <label class="text-xs text-ink-500" for="engagementMin">Engagement at least (%)</label>
                <input id="engagementMin" type="number" min="0" step="0.1" wire:model.live.debounce.400ms="engagementMin" placeholder="e.g. 3" class="input mt-1 py-1.5">
            </div>
            <p class="sm:col-span-5 text-[11px] text-ink-400">Median, average and engagement filters only match profiles with verified numbers.</p>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 mb-2 text-xs text-ink-500 tnum">
        <span wire:loading.remove>Showing {{ $creators->firstItem() ?? 0 }}–{{ $creators->lastItem() ?? 0 }} of {{ number_format($creators->total()) }}</span>
        <span wire:loading>Updating…</span>
    </div>

    @if($creators->isEmpty())
        <div class="card p-10 text-center">
            <p class="font-medium text-ink-900">No creators match these filters.</p>
            <p class="mt-1 text-sm text-ink-500">Know a creator who should be on here? <a href="{{ route('creators.create') }}" class="underline underline-offset-2">Add them</a>.</p>
        </div>
    @elseif($view === 'cards')
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3" wire:loading.class="opacity-60">
            @foreach($creators as $creator)
                <x-creator-card :creator="$creator" wire:key="creator-{{ $creator->id }}" />
            @endforeach
        </div>
    @else
        @include('creators.partials.directory-table', ['creators' => $creators, 'platform' => $activePlatform, 'offset' => ($creators->currentPage() - 1) * $creators->perPage()])
    @endif

    <div class="mt-6">{{ $creators->links() }}</div>
</div>
