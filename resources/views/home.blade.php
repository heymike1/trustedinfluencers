<x-layouts.app :canonical="route('home')" :json-ld="\App\Support\Seo::website()">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-8 pb-9 sm:pt-10 sm:pb-11 flex flex-col items-center text-center gap-4">
                <h1 class="display max-w-[34rem] text-[28px] leading-[1.1] sm:text-[34px] lg:text-[36px]">Which creator actually gets watched?</h1>
                <h2 class="max-w-xl text-[14px] font-medium text-ink-600">{{ config('app.tagline') }}</h2>

                <form method="GET" action="{{ route('creators.index') }}" class="w-full max-w-xl">
                    <label for="hero-search" class="sr-only">Search creators</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 size-[18px] text-ink-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="9" r="6"/><path d="M13.5 13.5L17 17"/></svg>
                        <input id="hero-search" type="search" name="q" autocomplete="off"
                               placeholder="Search {{ number_format($counts['creators']) }} creators by name, handle or category"
                               class="input h-[52px] w-full rounded-full border-band-edge pl-12 pr-5 text-[15px] shadow-[0_6px_20px_rgba(13,35,82,0.06)]">
                    </div>
                </form>

                <div class="flex flex-wrap justify-center gap-1.5">
                    @foreach($categories->take(6) as $category)
                        <a href="{{ route('creators.index', ['category' => $category->slug]) }}" class="chip">{{ $category->name }}</a>
                    @endforeach
                    <a href="{{ route('creators.index') }}" class="chip">All categories <svg class="size-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 8l5 5 5-5"/></svg></a>
                </div>

                <p class="text-[13px] text-ink-600 tnum">Numbers come straight from {{ \App\Enums\Platform::enabledLabels(' and ') }}. <span class="font-semibold text-brand-700">{{ number_format($counts['verified']) }}</span> {{ \Illuminate\Support\Str::plural('profile', $counts['verified']) }} verified so far.</p>
            </div>
        </div>
    </x-slot:hero>

    {{-- What moved lately, as one line. The full picture lives in the leaderboard below. --}}
    <p class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[13px] text-ink-500 tnum">
        <span class="size-1.5 shrink-0 rounded-full bg-brand-600"></span>
        @if($pulse['refreshedToday'])<span><span class="font-semibold text-ink-950">{{ number_format($pulse['refreshedToday']) }}</span> {{ \Illuminate\Support\Str::plural('profile', $pulse['refreshedToday']) }} refreshed today</span>@else<span>Numbers refresh every {{ config('social.sync.refresh_every_hours') }} hours</span>@endif
        @if($new = $pulse['newThisWeek'])
            <span class="text-ink-300">·</span>
            <span>new this week: <a href="{{ route('creators.show', $new) }}" class="font-semibold text-ink-950 hover:text-brand-700">{{ $new->name }}</a></span>
        @endif
        @if($best = $pulse['record'])
            <span class="text-ink-300">·</span>
            <span>{{ $best['title'] }}: <a href="{{ route('creators.show', $best['creator']) }}" class="font-semibold text-ink-950 hover:text-brand-700">{{ $best['creator']->name }}</a> <span class="text-ink-500">({{ $best['value'] }})</span></span>
        @endif
    </p>

    <livewire:top-performers />

    <p class="mx-auto mt-6 max-w-2xl text-center text-[13px] text-ink-500 text-pretty">{{ config('app.name') }} never asks for passwords. A creator signs in with {{ \App\Enums\Platform::enabledLabels(' or ') }} itself, and we read only that account’s own statistics to show verified numbers. Creators can disconnect at any time.</p>
    <p class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm text-ink-500">
        <span class="text-xs">Every number above comes straight from</span>
        @foreach(\App\Enums\Platform::enabled() as $platform)
            <span class="inline-flex items-center gap-2 font-semibold text-ink-700"><x-platform-icon :platform="$platform" class="size-[18px]" /> {{ $platform->label() }} {{ $platform === \App\Enums\Platform::Instagram ? 'Insights' : 'Analytics' }}</span>
        @endforeach
    </p>

    <section class="mt-20 grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div>
            <div class="flex items-baseline justify-between mb-3">
                <h2 class="display text-[22px] tracking-[-0.02em]">Recently claimed</h2>
                <a href="{{ route('creators.index', ['claimed' => 'claimed', 'sort' => 'newest']) }}" class="text-xs text-ink-500 hover:text-ink-950">All claimed profiles →</a>
            </div>
            <div class="card divide-y divide-ink-100 overflow-hidden">
                @forelse($recentlyVerified as $creator)
                    @php($account = $creator->primaryAccount())
                    @php($perf = $account?->hasVerifiedMetrics() ? $account->performanceFor($account->platform->primaryContentType()) : null)
                    <a href="{{ route('creators.show', $creator) }}" class="flex items-center gap-3 px-4 py-3.5 hover:bg-ink-50">
                        <x-avatar :creator="$creator" size="sm" />
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-1.5 text-sm font-semibold text-ink-950 truncate">{{ $creator->name }} <span class="inline-flex items-center gap-1 font-normal text-ink-500">@if($account)<x-platform-icon :platform="$account->platform" class="size-3.5" :colored="true" /> {{ $account->handleWithAt() }}@endif @if($creator->category)<span class="text-ink-300">·</span> {{ $creator->category->name }}@endif</span></span>
                            <span class="block text-xs text-ink-500 truncate tnum">
                                @if($perf)
                                    {{ \App\Support\Format::compact($perf->median_views) }} median views
                                    @if($perf->average_view_percentage) · {{ \App\Support\Format::percent($perf->average_view_percentage, 0) }} of each video watched @endif
                                    @if($perf->median_reach) · {{ \App\Support\Format::compact($perf->median_reach) }} median reach @endif
                                    · {{ \App\Support\Format::percent($perf->engagement_rate) }} engagement
                                @else
                                    Claimed
                                @endif
                                · {{ \App\Support\Format::compact($account?->follower_count) }} {{ $account?->platform->audienceNoun() }}
                            </span>
                        </span>
                        <x-state-badge :state="$creator->profileState()" />
                        <span class="w-12 text-right text-xs text-ink-400 tnum">{{ $creator->claimed_at?->diffForHumans(short: true, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}</span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-ink-500">Nobody has claimed a profile yet. <a href="{{ route('creators.index') }}" class="underline">Find yours</a>.</p>
                @endforelse
            </div>
        </div>
        <div class="card p-5 lg:mt-10">
            <h2 class="text-[15px] font-semibold text-ink-950">What a verified profile shows</h2>
            <p class="mt-0.5 text-xs text-ink-500">Everything below comes straight from the platform. Not every platform shares every number.</p>
            <ul class="mt-3 space-y-2 text-[13.5px] text-ink-900">
                @foreach(\App\Support\PlatformFacts::metrics() as $metric)
                    <li class="flex justify-between gap-3"><span>{{ $metric['what'] }}</span><span class="text-xs text-ink-500 whitespace-nowrap">{{ $metric['where'] }}</span></li>
                @endforeach
            </ul>
            <p class="mt-3 border-t border-ink-100 pt-3 text-xs text-ink-500">Some things the platforms don’t share, so we never show them: {{ \App\Support\PlatformFacts::notShared() }}.</p>
        </div>
    </section>

    <section class="mt-20">
        <div class="flex items-end justify-between mb-4">
            <h2 class="display text-[22px] tracking-[-0.02em]">Browse by category</h2>
            <a href="{{ route('creators.index') }}" class="text-xs text-ink-500 hover:text-ink-950">All creators →</a>
        </div>
        <div class="grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $category)
                <a href="{{ route('creators.index', ['category' => $category->slug]) }}" class="card flex items-center gap-3 p-3.5 hover:border-brand-200 transition-colors">
                    <span class="flex size-[38px] shrink-0 items-center justify-center rounded-[10px] border border-ink-200 bg-ink-50 text-ink-900"><x-category-icon :slug="$category->slug" class="size-[18px]" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-ink-950">{{ $category->name }}</span>
                        <span class="block text-xs text-ink-500 tnum">{{ $category->creators_count }} {{ \Illuminate\Support\Str::plural('creator', $category->creators_count) }} · {{ $category->verified_count }} verified</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
