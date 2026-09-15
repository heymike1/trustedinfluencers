<x-layouts.app :canonical="route('home')" :json-ld="\App\Support\Seo::website()">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-36 sm:pt-14 sm:pb-40 flex flex-col items-center text-center gap-5">
                <h1 class="display max-w-2xl text-3xl leading-[1.1] sm:text-4xl lg:text-[40px]">{{ config('app.tagline') }}</h1>
                <p class="max-w-xl text-base text-ink-700 text-pretty">Brands find creators by real numbers, not follower counts. Creators claim their profile and the stats come straight from YouTube, Instagram or X.</p>
                <div class="flex items-center gap-8 tnum">
                    <span class="flex flex-col items-center"><span class="display text-2xl tracking-[-0.02em]">{{ number_format($counts['creators']) }}</span><span class="text-xs font-medium text-ink-500">creators</span></span>
                    <span class="h-8 w-px bg-band-edge"></span>
                    <span class="flex flex-col items-center"><span class="display text-2xl tracking-[-0.02em] !text-brand-700">{{ number_format($counts['verified']) }}</span><span class="text-xs font-medium text-ink-500">verified</span></span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('creators.index') }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Browse creators <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                    <a href="{{ route('creators.create') }}" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">Add a creator</a>
                </div>
            </div>
        </div>
    </x-slot:hero>

    {{-- Three moments in time, overlapping the band. Not filters: a glance at what moved today, this week and ever. --}}
    <div class="-mt-36 sm:-mt-40 card overflow-hidden grid md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-ink-100">
        <div class="px-6 py-5 flex flex-col gap-3">
            <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500"><span class="size-1.5 rounded-full bg-brand-600"></span> Today</p>
            @if($pulse['refreshedToday'] || $pulse['claimedToday'])
                <p class="display text-[28px] leading-none tracking-[-0.02em] tnum">{{ number_format($pulse['refreshedToday']) }}<span class="ml-2 text-sm font-semibold tracking-normal text-ink-700">{{ \Illuminate\Support\Str::plural('profile', $pulse['refreshedToday']) }} refreshed</span></p>
                <p class="text-[13px] text-ink-500 tnum">{{ $pulse['claimedToday'] ? number_format($pulse['claimedToday']).' '.\Illuminate\Support\Str::plural('creator', $pulse['claimedToday']).' claimed a profile today.' : 'Nobody new yet today.' }} Numbers come in fresh every {{ config('social.sync.refresh_every_hours') }} hours.</p>
            @else
                <p class="display text-[28px] leading-none tracking-[-0.02em]">Quiet so far</p>
                <p class="text-[13px] text-ink-500">Numbers come in fresh every {{ config('social.sync.refresh_every_hours') }} hours. <a href="{{ route('creators.index') }}" class="font-semibold text-brand-700">Find your profile</a> to be today’s first.</p>
            @endif
        </div>
        <div class="px-6 py-5 flex flex-col gap-3">
            <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500"><span class="size-1.5 rounded-full bg-brand-600"></span> This week <span class="normal-case tracking-normal font-medium text-ink-400">· strongest newcomer</span></p>
            @if($new = $pulse['newThisWeek'])
                @php($account = $new->primaryAccount())
                <a href="{{ route('creators.show', $new) }}" class="flex items-center gap-3 group">
                    <x-avatar :creator="$new" size="md" />
                    <span class="min-w-0">
                        <span class="block display text-lg leading-tight tracking-[-0.02em] truncate group-hover:text-brand-700">{{ $new->name }}</span>
                        <span class="block text-[13px] text-ink-500 tnum">{{ \App\Support\Format::compact($new->median_views) }} median views @if($account)· <x-platform-icon :platform="$account->platform" class="inline size-3" :colored="true" /> {{ $account->handleWithAt() }}@endif</span>
                    </span>
                </a>
            @else
                <p class="display text-[28px] leading-none tracking-[-0.02em]">Nobody new yet</p>
                <p class="text-[13px] text-ink-500">The strongest profile to join this week shows up here. <a href="{{ route('creators.create') }}" class="font-semibold text-brand-700">Add a creator</a> you work with.</p>
            @endif
        </div>
        <div class="px-6 py-5 flex flex-col gap-3">
            <p class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500"><span class="size-1.5 rounded-full bg-brand-600"></span> All-time <span class="normal-case tracking-normal font-medium text-ink-400">· most watched</span></p>
            @if($best = $pulse['mostWatched'])
                <a href="{{ route('creators.show', $best) }}" class="flex items-center gap-3 group">
                    <x-avatar :creator="$best" size="md" />
                    <span class="min-w-0">
                        <span class="block display text-lg leading-tight tracking-[-0.02em] truncate group-hover:text-brand-700">{{ $best->name }}</span>
                        <span class="block text-[13px] text-ink-500 tnum"><span class="font-semibold text-brand-700">{{ \App\Support\Format::percent($best->average_view_percentage, 0) }}</span> of each video watched, on average</span>
                    </span>
                </a>
            @else
                <p class="display text-[28px] leading-none tracking-[-0.02em]">No record yet</p>
                <p class="text-[13px] text-ink-500">The creator whose videos get watched the longest takes this spot.</p>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <livewire:top-performers />
    </div>

    <p class="mt-6 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm text-ink-500">
        <span class="text-xs">Every number above comes straight from</span>
        @foreach(\App\Enums\Platform::cases() as $platform)
            <span class="inline-flex items-center gap-2 font-semibold text-ink-700"><x-platform-icon :platform="$platform" class="size-[18px]" /> {{ $platform->label() }} {{ $platform === \App\Enums\Platform::Instagram ? 'Insights' : 'Analytics' }}</span>
        @endforeach
    </p>

    <section class="mt-20 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
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
                @foreach([
                    ['Median and average views', 'YT · IG · X'],
                    ['How much of a video gets watched', 'YT'],
                    ['Average watch time', 'YT · IG'],
                    ['How fast views come in', 'YT'],
                    ['Reach, and how many viewers are new', 'IG'],
                    ['Audience age, gender and country', 'YT · IG'],
                    ['Saves, shares and comments', 'YT · IG · X'],
                    ['Profile visits and link clicks', 'IG · X'],
                    ['How regularly they post', 'YT · IG · X'],
                ] as [$what, $where])
                    <li class="flex justify-between gap-3"><span>{{ $what }}</span><span class="text-xs text-ink-500 whitespace-nowrap">{{ $where }}</span></li>
                @endforeach
            </ul>
            <p class="mt-3 border-t border-ink-100 pt-3 text-xs text-ink-500">Some things the platforms don’t share, so we never show them: impressions on YouTube, audience details on X, second-by-second watch data on Instagram.</p>
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
