@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'noindex' => null,
    'image' => null,
    'jsonLd' => null,
    'wide' => false,
    'band' => false,
])
@php
    $siteName = config('app.name');
    // The home title is the bare app name: Google's OAuth branding check matches it against the consent screen.
    $fullTitle = $title ? $title.' · '.$siteName : $siteName;
    $description ??= config('app.tagline').' Browse creators on '.\App\Enums\Platform::enabledLabels(' and ').'. Claimed profiles show numbers that come straight from the creator’s own account.';
    $image = $image ?? config('app.og_image');
    $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : url($image)) : null;
    // Signed-in areas, auth screens and OAuth hops never belong in a search index.
    $hasLogo = file_exists(public_path('logo.png'));
    $noindex ??= request()->routeIs('account*', 'admin.*', 'login*', 'register', 'password.*', 'oauth.*', 'creators.claim*');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
    @if($canonical && ! $noindex)
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    <meta name="theme-color" content="#0d2352">
    <meta name="application-name" content="{{ $siteName }}">

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $jsonLd['@type'] ?? '' === 'ProfilePage' ? 'profile' : 'website' }}">
    <meta property="og:title" content="{{ $title ?? $siteName }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:locale" content="en_US">
    @if($imageUrl)
        <meta property="og:image" content="{{ $imageUrl }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="{{ $imageUrl ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title ?? $siteName }}">
    <meta name="twitter:description" content="{{ $description }}">
    @if($imageUrl)<meta name="twitter:image" content="{{ $imageUrl }}">@endif
    @if(config('app.twitter_handle'))<meta name="twitter:site" content="{{ config('app.twitter_handle') }}">@endif

    <link rel="icon" href="/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="/logo.png">
    <link rel="manifest" href="/site.webmanifest">

    @if($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col overflow-x-clip">
    @php
        $navLinks = [
            ['href' => route('home'), 'label' => 'Home', 'on' => request()->routeIs('home')],
            ['href' => route('creators.index'), 'label' => 'Browse creators', 'on' => request()->routeIs('creators.index', 'creators.show', 'creators.create')],
            ['href' => route('about'), 'label' => 'About', 'on' => request()->routeIs('about')],
        ];
    @endphp
    {{-- The nav is a pill floating on the band (or on the page ground when a page has no band). Livewire pages set `band` and draw their own <x-page-band>. --}}
    <header class="{{ isset($hero) || $band ? 'bg-band' : 'bg-page' }}" id="site-header">
        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 pt-3 sm:pt-4">
            <div class="flex h-14 items-center justify-between gap-4 rounded-full border border-band-edge bg-white pl-4 pr-2 shadow-[0_6px_20px_rgba(13,35,82,0.08)] sm:h-[60px] sm:pl-5 sm:pr-2.5">
                <a href="{{ route('home') }}" class="display text-[17px] tracking-[-0.02em] flex items-center gap-2.5 whitespace-nowrap">
                    @if($hasLogo)<img src="/logo.png" alt="{{ config('app.name') }}" class="size-6 shrink-0">@else<span class="inline-flex size-6 items-center justify-center rounded-[7px] bg-brand-700"><svg class="size-3.5 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg></span>@endif
                    {{ config('app.name') }}
                </a>
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                    @foreach($navLinks as $link)
                        <a href="{{ $link['href'] }}" class="rounded-full px-3.5 py-2 {{ $link['on'] ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-700 hover:bg-ink-50 hover:text-ink-950' }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
                <nav class="hidden md:flex items-center gap-1.5 text-sm font-medium whitespace-nowrap">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="rounded-full px-3.5 py-2 {{ request()->routeIs('admin.*') ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-700 hover:bg-ink-50' }}">Admin</a>
                        @endif
                        <a href="{{ route('account') }}" class="rounded-full px-3.5 py-2 {{ request()->routeIs('account*') ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-700 hover:bg-ink-50' }}">My profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full px-3.5 py-2 text-ink-700 hover:bg-ink-50">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-3.5 py-2 text-ink-700 hover:bg-ink-50">Sign in</a>
                        <a href="{{ route('register') }}" class="btn-primary !py-2.5">Claim your profile <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                    @endauth
                </nav>
                <button type="button" data-menu-toggle aria-expanded="false" aria-controls="site-menu" aria-label="Menu" class="md:hidden inline-flex size-10 items-center justify-center rounded-full text-ink-950 hover:bg-ink-50">
                    <svg data-menu-icon="closed" class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg data-menu-icon="open" hidden class="size-[22px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>

            {{-- Mobile menu: a card dropping out of the pill. --}}
            <div id="site-menu" hidden class="md:hidden absolute inset-x-4 top-full z-30 mt-2 rounded-[20px] border border-band-edge bg-white px-4 pb-4 pt-1 shadow-[0_12px_32px_rgba(13,35,82,0.10)]">
                @foreach($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="flex h-[52px] items-center justify-between border-b border-ink-100 px-1 text-[17px] font-semibold {{ $link['on'] ? 'text-brand-700' : 'text-ink-950' }}">{{ $link['label'] }} <svg class="size-4 text-ink-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 5l5 5-5 5"/></svg></a>
                @endforeach
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="flex h-[52px] items-center justify-between border-b border-ink-100 px-1 text-[17px] font-semibold text-ink-950">Admin <svg class="size-4 text-ink-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 5l5 5-5 5"/></svg></a>
                    @endif
                    <a href="{{ route('account') }}" class="flex h-[52px] items-center justify-between px-1 text-[17px] font-semibold text-ink-950">My profile <svg class="size-4 text-ink-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 5l5 5-5 5"/></svg></a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn-secondary w-full !py-3 !text-[15px]">Log out</button>
                    </form>
                @else
                    <div class="mt-3 flex flex-col gap-2">
                        <a href="{{ route('register') }}" class="btn-primary !py-3 !text-[15px]">Claim your profile</a>
                        <a href="{{ route('login') }}" class="btn-secondary !py-3 !text-[15px]">Sign in</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>
    <script>
        (function () {
            var toggle = document.querySelector('[data-menu-toggle]'), menu = document.getElementById('site-menu');
            if (!toggle || !menu) return;
            function set(open) {
                menu.hidden = !open;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.querySelector('[data-menu-icon="closed"]').hidden = open;
                toggle.querySelector('[data-menu-icon="open"]').hidden = !open;
            }
            toggle.addEventListener('click', function () { set(menu.hidden); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
        })();
    </script>

    @php($sponsorSlots = \App\Models\SponsorSlot::live()->orderBy('sort_order')->orderBy('id')->get()->groupBy('side'))
    <main class="relative flex-1">
        <x-sponsor-rail :slots="$sponsorSlots->get('left', collect())" side="left" />
        <x-sponsor-rail :slots="$sponsorSlots->get('right', collect())" side="right" />
        {{ $hero ?? '' }}
        <div class="mx-auto {{ $wide ? 'max-w-7xl' : 'max-w-6xl' }} px-4 sm:px-6 py-8">
            <x-flash />
            {{ $slot }}
        </div>
    </main>

    <footer class="border-t border-ink-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-10">
            <div class="grid gap-8 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div class="max-w-xs">
                    <a href="{{ route('home') }}" class="font-semibold tracking-tight text-ink-950 flex items-center gap-2">
                        @if($hasLogo)<img src="/logo.png" alt="{{ config('app.name') }}" class="size-6 shrink-0">@else<span class="inline-block size-5 rounded-md bg-brand-700"></span>@endif
                        {{ config('app.name') }}
                    </a>
                    <p class="mt-3 text-sm text-ink-600">{{ config('app.tagline') }}</p>
                    <p class="mt-3 text-xs text-ink-500">“Verified” means the creator signed in with their own account and the numbers came straight from the platform. We never ask for passwords and creators can disconnect at any time.</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Browse</p>
                    <ul class="mt-3 space-y-2 text-sm text-ink-700">
                        <li><a href="{{ route('creators.index') }}" class="hover:text-ink-950">All creators</a></li>
                        <li><a href="{{ route('creators.index', ['verified' => 1]) }}" class="hover:text-ink-950">Verified only</a></li>
                        <li><a href="{{ route('home') }}#leaderboard" class="hover:text-ink-950">Leaderboard</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-ink-950">About</a></li>
                        @foreach(\App\Enums\Platform::enabled() as $platform)
                            <li><a href="{{ route('creators.index', ['platform' => $platform->value]) }}" class="inline-flex items-center gap-1.5 hover:text-ink-950"><x-platform-icon :platform="$platform" class="size-3.5" :colored="true" /> {{ $platform->label() }} creators</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">For creators</p>
                    <ul class="mt-3 space-y-2 text-sm text-ink-700">
                        <li><a href="{{ route('creators.index') }}" class="hover:text-ink-950">Find your profile</a></li>
                        <li><a href="{{ route('creators.create') }}" class="hover:text-ink-950">Add yourself</a></li>
                        @auth
                            <li><a href="{{ route('account') }}" class="hover:text-ink-950">Your profile</a></li>
                            <li><a href="{{ route('account.connections') }}" class="hover:text-ink-950">Connected accounts</a></li>
                        @else
                            <li><a href="{{ route('register') }}" class="hover:text-ink-950">Claim your profile</a></li>
                            <li><a href="{{ route('login') }}" class="hover:text-ink-950">Sign in</a></li>
                        @endauth
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-ink-500">For brands</p>
                    <ul class="mt-3 space-y-2 text-sm text-ink-700">
                        <li><a href="{{ route('creators.index', ['verified' => 1, 'sort' => 'median_views']) }}" class="hover:text-ink-950">Highest median views</a></li>
                        <li><a href="{{ route('creators.index', ['verified' => 1, 'sort' => 'engagement']) }}" class="hover:text-ink-950">Highest engagement</a></li>
                        <li><a href="{{ route('creators.create') }}" class="hover:text-ink-950">Add a creator you work with</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 flex flex-wrap items-center justify-between gap-2 border-t border-ink-100 pt-4 text-xs text-ink-400">
                <p class="flex gap-4">
                    <span>© {{ date('Y') }} {{ config('app.name') }}</span>
                    <a href="{{ route('privacy') }}" class="hover:text-ink-900">Privacy</a>
                    <a href="{{ route('terms') }}" class="hover:text-ink-900">Terms</a>
                    @if(config('services.datafast.website_id'))<button type="button" data-cookie-settings class="hover:text-ink-900">Cookie settings</button>@endif
                </p>
                <p>Not affiliated with YouTube, Instagram or X. Platform names and icons belong to their owners.</p>
            </div>
        </div>
    </footer>

    @if(config('services.datafast.website_id'))
        <x-cookie-banner />
    @endif
</body>
</html>
