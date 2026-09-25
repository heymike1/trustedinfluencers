@php
    $features = \App\Support\PlatformFacts::metrics();
@endphp
<x-layouts.app title="About" description="Trusted Influencers is a public creator directory where only the creator can unlock the numbers a sponsor needs: median views, reach and audience, straight from Instagram or X." :canonical="route('about')">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-12 sm:pt-16 sm:pb-16 flex flex-col gap-6">
                <h1 class="display max-w-3xl text-3xl leading-[1.1] sm:text-4xl lg:text-[40px]">Follower counts are easy to fake. Watch time isn’t.</h1>
                <p class="max-w-2xl text-base text-ink-700 sm:text-lg text-pretty">{{ config('app.name') }} is a public directory where the creator, and only the creator, unlocks the numbers a sponsor actually needs: median views, how much of a video gets watched, and who the audience is. Straight from {{ \App\Enums\Platform::enabledLabels(' and ') }}, refreshed every {{ config('social.sync.refresh_every_hours') }} hours.</p>
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
                            <x-badge variant="verified" class="gap-1.5"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified through <x-platform-icon platform="instagram" class="size-3" :colored="true" /> Instagram</x-badge>
                        </div>
                        <p class="mt-0.5 text-[13px] text-ink-500">@johnsmith · Finance · 124K followers</p>
                    </div>
                </div>
                <p class="max-w-md text-sm text-ink-500">Personal finance, explained without the jargon. Weekly reels on saving, investing and getting out of debt.</p>
                <div class="grid grid-cols-3 gap-2.5 tnum">
                    @foreach([['Median reach', '68K', 'last 20 reels'], ['New viewers', '57%', 'of everyone reached'], ['Engagement', '4.1%', 'likes, comments, saves']] as [$label, $value, $hint])
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
                    <div class="flex items-center justify-between text-[13.5px]"><span class="font-semibold text-ink-950">Who the reach came from</span><span class="text-xs text-ink-500">Last 20 reels</span></div>
                    <div class="mt-4 flex h-3 overflow-hidden rounded-full" aria-hidden="true">
                        <div class="bg-brand-200" style="width: 43%"></div>
                        <div class="bg-brand-600" style="width: 57%"></div>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-ink-500 tnum">
                        <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand-200"></span>Followers · 43%</span>
                        <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand-600"></span>New people · 57%</span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-t border-ink-100 pt-3 text-xs tnum">
                        <div><p class="text-ink-500">Average watch time</p><p class="mt-0.5 text-sm font-semibold text-ink-950">14s of 22s</p></div>
                        <div><p class="text-ink-500">Saves per 1,000 reached</p><p class="mt-0.5 text-sm font-semibold text-ink-950">8.4</p></div>
                    </div>
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
            @foreach(\App\Enums\Platform::enabled() as $platform)
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
            <p class="text-base text-ink-500 text-pretty">How far a post actually reaches, who those people are and where they live isn’t visible on any public page. Anything that isn’t verified is clearly marked as such.</p>
            <a href="{{ route('creators.index', ['verified' => 1]) }}" class="inline-flex items-center gap-1.5 text-[15px] font-semibold text-brand-700">See verified profiles <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card p-5">
                <div class="flex items-center justify-between"><p class="text-[15px] font-semibold text-ink-950">Unclaimed</p><x-badge>Public info only</x-badge></div>
                <dl class="mt-4 space-y-2.5 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Followers</dt><dd class="font-semibold">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median reach</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">New viewers</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="text-ink-300">—</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="text-ink-300">—</dd></div>
                </dl>
            </div>
            <div class="card p-5 border-brand-200 ring-4 ring-verified-50">
                <div class="flex items-center justify-between"><p class="text-[15px] font-semibold text-ink-950">Claimed</p><x-state-badge :state="\App\Enums\ProfileState::VerifiedMetrics" /></div>
                <dl class="mt-4 space-y-2.5 text-sm tnum">
                    <div class="flex justify-between"><dt class="text-ink-500">Followers</dt><dd class="font-semibold">124K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Median reach</dt><dd class="font-semibold">68K</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">New viewers</dt><dd class="font-semibold">57%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">Viewers aged 25–34</dt><dd class="font-semibold">41%</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-500">UK viewers</dt><dd class="font-semibold">38%</dd></div>
                </dl>
                <p class="mt-3 text-xs font-semibold text-brand-700">Verified through Instagram</p>
            </div>
        </div>
    </section>

    <section class="mt-24">
        <div class="flex flex-col items-center text-center gap-3">
            <h2 class="display text-3xl sm:text-[40px] leading-[1.1]">…and everything else the platform shares</h2>
            <p class="max-w-lg text-base text-ink-500">Not every platform shares every number. Where it doesn’t, we show nothing rather than a guess.</p>
        </div>
        <div class="mt-9 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($features as $feature)
                <div class="card flex flex-col items-center gap-3.5 px-5 pt-6 pb-5 text-center">
                    <span class="flex size-11 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-900"><svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $feature['icon'] !!}</svg></span>
                    <span class="text-sm font-semibold text-ink-950">{{ $feature['what'] }}</span>
                    <span class="text-xs text-ink-500">{{ $feature['whereLong'] }}</span>
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
