<x-layouts.app :canonical="route('home')" :json-ld="\App\Support\Seo::website()">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-14">
                <h1 class="max-w-2xl text-3xl font-semibold tracking-tight text-ink-950 leading-[1.15] sm:text-4xl">{{ config('app.tagline') }}</h1>
                <p class="mt-3 max-w-xl text-base text-ink-700">Brands find creators by real numbers, not follower counts. Creators claim their profile and their stats come straight from YouTube, Instagram or X.</p>
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <a href="{{ route('creators.index') }}" class="btn-primary">Browse creators</a>
                    <a href="{{ route('creators.create') }}" class="btn-secondary border-band-edge">Add a creator</a>
                    <span class="text-sm text-ink-600 tnum sm:ml-2">{{ number_format($counts['creators']) }} creators · <span class="text-brand-700 font-medium">{{ number_format($counts['verified']) }} verified</span></span>
                </div>
            </div>
        </div>
    </x-slot:hero>

    <div class="flex items-start gap-3 rounded-md border border-verified-100 bg-verified-50 px-4 py-3 text-sm text-verified-600">
        <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg>
        <p><span class="font-semibold">Verified numbers come straight from the creator’s own YouTube, Instagram or X account</span>, including how long people watch and who the audience is, which you can’t see on a public page. Anything that isn’t verified is clearly marked.</p>
    </div>

    <div class="mt-10">
        <livewire:top-performers />
    </div>

    <section class="mt-12 grid gap-6 lg:grid-cols-[1fr_360px] lg:items-start">
        <div>
            <div class="flex items-baseline justify-between mb-3">
                <h2 class="text-base font-semibold text-ink-950">Recently claimed</h2>
                <a href="{{ route('creators.index', ['claimed' => 'claimed', 'sort' => 'newest']) }}" class="text-xs text-ink-500 hover:text-ink-950">All claimed profiles →</a>
            </div>
            <div class="card divide-y divide-ink-100">
                @forelse($recentlyVerified as $creator)
                    @php($account = $creator->primaryAccount())
                    @php($perf = $account?->hasVerifiedMetrics() ? $account->performanceFor($account->platform->primaryContentType()) : null)
                    <a href="{{ route('creators.show', $creator) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-ink-50">
                        <x-avatar :creator="$creator" size="sm" />
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-1.5 text-sm font-semibold text-ink-950 truncate">{{ $creator->name }} <span class="inline-flex items-center gap-1 font-normal text-ink-500">@if($account)<x-platform-icon :platform="$account->platform" class="size-3.5" :colored="true" /> {{ $account->handleWithAt() }}@endif @if($creator->category)<span class="text-ink-300">·</span> {{ $creator->category->name }}@endif</span></span>
                            <span class="block text-xs text-ink-500 truncate tnum">
                                @if($perf)
                                    {{ \App\Support\Format::compact($perf->median_views) }} median views
                                    @if($perf->average_view_percentage) · {{ \App\Support\Format::percent($perf->average_view_percentage, 0) }} of each video watched @endif
                                    @if($perf->median_reach) · {{ \App\Support\Format::compact($perf->median_reach) }} median reach @endif
                                    · {{ \App\Support\Format::percent($perf->engagement_rate) }} engagement
                                @elseif($creator->socialAccounts->contains->isImporting())
                                    Importing numbers
                                @elseif($creator->socialAccounts->contains->hasVerifiedMetrics())
                                    Connected, nothing to measure yet
                                @else
                                    Claimed, no account connected
                                @endif
                                · {{ \App\Support\Format::compact($account?->follower_count) }} {{ $account?->platform->audienceNoun() }}
                            </span>
                        </span>
                        <x-state-badge :state="$creator->profileState()" />
                        <span class="w-16 text-right text-xs text-ink-400">{{ $creator->claimed_at?->diffForHumans(short: true) }}</span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-ink-500">Nobody has claimed a profile yet. <a href="{{ route('creators.index') }}" class="underline">Find yours</a>.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-sm font-semibold text-ink-950">What a verified profile shows</h2>
            <p class="mt-0.5 text-xs text-ink-500">Everything below comes straight from the platform. Not every platform shares every number.</p>
            <ul class="mt-3 space-y-1.5 text-[13px] text-ink-700">
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
                    <li class="flex justify-between gap-3"><span>{{ $what }}</span><span class="text-ink-500 whitespace-nowrap">{{ $where }}</span></li>
                @endforeach
            </ul>
            <p class="mt-3 border-t border-ink-100 pt-3 text-xs text-ink-500">Some things the platforms don’t share, so we never show them: impressions on YouTube, audience details on X, second-by-second watch data on Instagram.</p>
        </div>
    </section>

    <section class="mt-12 grid gap-10 lg:grid-cols-2 lg:items-start">
        <div>
            <h2 class="text-base font-semibold text-ink-950 mb-3">How a profile gets verified</h2>
            <ol class="space-y-3.5 text-sm text-ink-700">
                @foreach([
                    ['Anyone adds a creator.', 'A name, a platform and a handle is enough. The profile goes live straight away and only shows public info.'],
                    ['The creator claims it.', 'No screenshots, no email checks. They simply sign in with the social account itself.'],
                    ['It has to be the right account.', 'If the account they sign in with isn’t the one on the profile, the claim fails.'],
                    ['The numbers come from the platform.', 'Views, watch time, audience and engagement are pulled in automatically and refreshed every day.'],
                ] as $i => [$heading, $body])
                    <li class="flex gap-3"><span class="w-6 shrink-0 text-xs font-semibold text-ink-400 tnum">0{{ $i + 1 }}</span><span><span class="font-semibold text-ink-950">{{ $heading }}</span> {{ $body }}</span></li>
                @endforeach
            </ol>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="card p-4">
                <div class="flex items-center justify-between"><p class="text-sm font-semibold text-ink-950">Unclaimed</p><x-badge>Public info only</x-badge></div>
                <dl class="mt-4 space-y-2 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Subscribers</dt><dd class="font-medium">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median views</dt><dd class="text-ink-400">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Watched at halfway</dt><dd class="text-ink-400">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="text-ink-400">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="text-ink-400">—</dd></div>
                </dl>
            </div>
            <div class="card p-4 border-verified-100">
                <div class="flex items-center justify-between"><p class="text-sm font-semibold text-ink-950">Claimed</p><x-state-badge :state="\App\Enums\ProfileState::VerifiedMetrics" /></div>
                <dl class="mt-4 space-y-2 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Subscribers</dt><dd class="font-medium">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median views</dt><dd class="font-medium">68K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Watched at halfway</dt><dd class="font-medium">48%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="font-medium">41%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="font-medium">38%</dd></div>
                </dl>
                <p class="mt-3 text-xs text-verified-600">Verified through YouTube</p>
            </div>
        </div>
    </section>

    <section class="mt-12">
        <h2 class="text-base font-semibold text-ink-950 mb-3">Browse by category</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $category)
                <a href="{{ route('creators.index', ['category' => $category->slug]) }}" class="card flex items-center gap-3 p-3.5 hover:border-brand-200 transition-colors">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-700"><x-category-icon :slug="$category->slug" /></span>
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-ink-950">{{ $category->name }}</span>
                        <span class="block text-xs text-ink-500 tnum">{{ $category->creators_count }} {{ \Illuminate\Support\Str::plural('creator', $category->creators_count) }} · {{ $category->verified_count }} verified</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>
</x-layouts.app>
