@php
    $features = [
        ['Median and average views', 'YouTube · Instagram · X', '<path d="M3 15l4-5 3 3 4-6 3 4"/><path d="M3 17h14"/>'],
        ['How much of a video gets watched', 'YouTube', '<rect x="2.5" y="4.5" width="15" height="11" rx="2"/><path d="M8.5 8v4l3.5-2z"/>'],
        ['Average watch time', 'YouTube · Instagram', '<circle cx="10" cy="10" r="7"/><path d="M10 6.5V10l2.5 1.5"/>'],
        ['How fast views come in', 'YouTube', '<path d="M3 12l5-6 4 4 5-6"/><path d="M13 4h4v4"/>'],
        ['Reach, and how many viewers are new', 'Instagram', '<circle cx="7" cy="8" r="2.5"/><circle cx="14" cy="8" r="2.5"/><path d="M2.5 16c0-2.5 2-4 4.5-4s4.5 1.5 4.5 4M11.5 12c2.5 0 4.5 1.5 4.5 4"/>'],
        ['Audience age, gender and country', 'YouTube · Instagram', '<circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3c2.5 2.5 2.5 11.5 0 14M10 3c-2.5 2.5-2.5 11.5 0 14"/>'],
        ['Saves, shares and comments', 'YouTube · Instagram · X', '<path d="M5 3h10v14l-5-3-5 3z"/>'],
        ['Profile visits and link clicks', 'Instagram · X', '<path d="M8.5 11.5l3-3M7 13l-1.5 1.5a2.5 2.5 0 0 1-3.5-3.5L4 9.5M13 7l1.5-1.5a2.5 2.5 0 0 1 3.5 3.5L16 10.5"/>'],
    ];
@endphp
<x-layouts.app title="About" description="Trusted Influencers is a public creator directory where only the creator can unlock the numbers a sponsor needs: median views, watch time and audience, straight from YouTube, Instagram or X." :canonical="route('about')">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-12 sm:pt-16 sm:pb-16 flex flex-col gap-6">
                <span class="eyebrow self-start">About {{ config('app.name') }}</span>
                <h1 class="display max-w-3xl text-4xl leading-[1.05] sm:text-5xl lg:text-[64px]">Follower counts are easy to fake. Watch time isn’t.</h1>
                <p class="max-w-2xl text-base text-ink-700 sm:text-lg text-pretty">{{ config('app.name') }} is a public directory where the creator, and only the creator, unlocks the numbers a sponsor actually needs: median views, how much of a video gets watched, and who the audience is. Straight from the platform, refreshed every {{ config('social.sync.refresh_every_hours') }} hours.</p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('creators.index') }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Find your profile <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                    <a href="{{ route('creators.index') }}" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">Browse creators</a>
                </div>
                <p class="text-[13px] text-ink-500">Free for creators and brands. No screenshots, no media kits: just sign in with the account itself.</p>
            </div>
        </div>
    </x-slot:hero>

    {{-- An example of what a verified profile looks like. Illustrative numbers, labelled as such. --}}
    <div class="card p-3 !rounded-[22px]">
        <div class="relative rounded-[14px] border border-ink-100 bg-ink-50 p-5 sm:p-7 grid gap-6 lg:grid-cols-2">
            <span class="absolute right-4 top-4 rounded-full border border-ink-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-ink-500">Example</span>
            <div class="flex flex-col gap-5">
                <div class="flex items-center gap-3.5">
                    <span class="flex size-14 items-center justify-center rounded-full bg-brand-100 text-lg font-semibold text-brand-700">JS</span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="display text-[22px] tracking-[-0.02em]">John Smith</span>
                            <x-badge variant="verified" class="gap-1.5"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified through <x-platform-icon platform="youtube" class="size-3" :colored="true" /> YouTube</x-badge>
                        </div>
                        <p class="mt-0.5 text-[13px] text-ink-500">@johnsmith · Finance · 124K subscribers</p>
                    </div>
                </div>
                <p class="max-w-md text-sm text-ink-500">Personal finance, explained without the jargon. Weekly videos on saving, investing and getting out of debt.</p>
                <div class="grid grid-cols-3 gap-2.5 tnum">
                    @foreach([['Median views', '68K', 'last 20 videos'], ['Watched', '48%', 'of each video'], ['Engagement', '4.1%', 'likes + comments']] as [$label, $value, $hint])
                        <div class="card !rounded-xl px-4 py-3.5">
                            <p class="text-xs text-ink-500">{{ $label }}</p>
                            <p class="display text-2xl tracking-[-0.02em]">{{ $value }}</p>
                            <p class="text-[11.5px] text-ink-500">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="btn-primary btn-sm pointer-events-none">Contact John</span>
                    <span class="btn-secondary btn-sm pointer-events-none">Full profile</span>
                </div>
            </div>
            <div class="flex flex-col gap-3">
                <div class="card !rounded-xl px-5 py-4">
                    <div class="flex items-center justify-between text-[13.5px]"><span class="font-semibold text-ink-950">How much of a video gets watched</span><span class="text-xs text-ink-500">Average of last 20</span></div>
                    <svg class="mt-3 w-full" height="120" viewBox="0 0 480 120" preserveAspectRatio="none" aria-hidden="true">
                        <line x1="0" y1="119.5" x2="480" y2="119.5" stroke="#e3e6e3"/>
                        <line x1="0" y1="60" x2="480" y2="60" stroke="#eef0ee" stroke-dasharray="3 4"/>
                        <path d="M0 8 C 40 12, 70 30, 110 40 S 200 58, 260 66 S 380 82, 480 96 L 480 120 L 0 120 Z" fill="#e6f4ec"/>
                        <path d="M0 8 C 40 12, 70 30, 110 40 S 200 58, 260 66 S 380 82, 480 96" fill="none" stroke="#1f9d66" stroke-width="2.5"/>
                        <line x1="240" y1="0" x2="240" y2="120" stroke="#0f3d2e" stroke-dasharray="3 3"/>
                        <circle cx="240" cy="64" r="4" fill="#0f3d2e"/>
                    </svg>
                    <div class="mt-2 flex justify-between text-xs text-ink-500"><span>Start</span><span class="font-semibold text-brand-700">48% still watching at halfway</span><span>End</span></div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach([['Audience age', [['25–34', 41], ['35–44', 27]]], ['Top countries', [['United Kingdom', 38], ['United States', 22]]]] as [$title, $rows])
                        <div class="card !rounded-xl px-4 py-4 space-y-2 tnum">
                            <p class="text-[13.5px] font-semibold text-ink-950">{{ $title }}</p>
                            @foreach($rows as [$label, $pct])
                                <div class="flex justify-between text-[13px]"><span class="text-ink-500">{{ $label }}</span><span class="font-semibold">{{ $pct }}%</span></div>
                                <div class="h-1.5 rounded-full bg-ink-100"><div class="h-1.5 rounded-full bg-brand-600" style="width: {{ $pct }}%"></div></div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="mt-10 flex flex-wrap items-center gap-x-10 gap-y-3 border-y border-ink-200 py-5 text-sm">
        <span class="max-w-[220px] text-[13px] leading-snug text-ink-500">Numbers pulled straight from the platforms, never typed in by hand</span>
        <div class="flex flex-wrap items-center gap-x-10 gap-y-3 sm:ml-auto">
            @foreach(\App\Enums\Platform::cases() as $platform)
                <span class="inline-flex items-center gap-2 font-semibold text-ink-700"><x-platform-icon :platform="$platform" class="size-[22px]" /> {{ $platform->label() }} {{ $platform === \App\Enums\Platform::Instagram ? 'Insights' : 'Analytics' }}</span>
            @endforeach
        </div>
    </div>

    <section class="mt-24">
        <div class="flex flex-col items-center text-center gap-4">
            <span class="eyebrow">How it works</span>
            <h2 class="display max-w-2xl text-3xl sm:text-[44px] leading-[1.1]">Verified in three steps, none of them a screenshot</h2>
            <p class="max-w-xl text-base text-ink-500 sm:text-[17px] text-pretty">Anyone can list a creator. Only the creator can verify it, and only the platform supplies the numbers.</p>
        </div>
        <div class="mt-10">
            @include('partials.how-it-works')
        </div>
    </section>

    <section class="mt-24 grid gap-10 lg:grid-cols-[420px_1fr] lg:items-center">
        <div class="flex flex-col gap-4">
            <span class="eyebrow self-start">Why claim</span>
            <h2 class="display text-3xl sm:text-[40px] leading-[1.1]">Public pages show followers. Verified profiles show what a sponsor actually gets.</h2>
            <p class="text-base text-ink-500 text-pretty">How long people watch, who they are and where they live isn’t visible on any public page. Anything that isn’t verified is clearly marked as such.</p>
            <a href="{{ route('creators.index', ['verified' => 1]) }}" class="inline-flex items-center gap-1.5 text-[15px] font-semibold text-brand-700">See verified profiles <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card p-5">
                <div class="flex items-center justify-between"><p class="text-[15px] font-semibold text-ink-950">Unclaimed</p><x-badge>Public info only</x-badge></div>
                <dl class="mt-4 space-y-2.5 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Subscribers</dt><dd class="font-semibold">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median views</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Watched at halfway</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="text-ink-300">—</dd></div>
                </dl>
            </div>
            <div class="card p-5 border-brand-200 ring-4 ring-verified-50">
                <div class="flex items-center justify-between"><p class="text-[15px] font-semibold text-ink-950">Claimed</p><x-state-badge :state="\App\Enums\ProfileState::VerifiedMetrics" /></div>
                <dl class="mt-4 space-y-2.5 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Subscribers</dt><dd class="font-semibold">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median views</dt><dd class="font-semibold">68K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Watched at halfway</dt><dd class="font-semibold">48%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="font-semibold">41%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="font-semibold">38%</dd></div>
                </dl>
                <p class="mt-3 text-xs font-semibold text-brand-700">Verified through YouTube</p>
            </div>
        </div>
    </section>

    <section class="mt-24">
        <div class="flex flex-col items-center text-center gap-3">
            <h2 class="display text-3xl sm:text-[40px] leading-[1.1]">…and everything else the platform shares</h2>
            <p class="max-w-lg text-base text-ink-500">Not every platform shares every number. Where it doesn’t, we show nothing rather than a guess.</p>
        </div>
        <div class="mt-9 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($features as [$label, $where, $icon])
                <div class="card flex flex-col items-center gap-3.5 px-5 pt-6 pb-5 text-center">
                    <span class="flex size-11 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-900"><svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg></span>
                    <span class="text-sm font-semibold text-ink-950">{{ $label }}</span>
                    <span class="text-xs text-ink-500">{{ $where }}</span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-24 mb-4">
        <div class="rounded-3xl bg-brand-700 p-8 sm:p-14 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-xl">
                <h2 class="display text-3xl sm:text-4xl leading-[1.1] !text-white">Are you a creator? Your profile might already be here.</h2>
                <p class="mt-3 text-base text-brand-200">Search your handle, sign in with the account, and your real numbers show up within minutes.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('creators.index') }}" class="btn !border-white !bg-white !px-5 !py-2.5 !text-[15px] text-brand-700 hover:!bg-brand-50">Find your profile <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                <a href="{{ route('creators.create') }}" class="btn !border-brand-600/60 !bg-transparent !px-5 !py-2.5 !text-[15px] !text-white hover:!bg-brand-800">Add yourself</a>
            </div>
        </div>
    </section>
</x-layouts.app>
