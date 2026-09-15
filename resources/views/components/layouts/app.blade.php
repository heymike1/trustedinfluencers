@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'noindex' => null,
    'image' => null,
    'jsonLd' => null,
    'wide' => false,
])
@php
    $siteName = config('app.name');
    $fullTitle = $title ? $title.' · '.$siteName : $siteName.' · '.config('app.tagline');
    $description ??= config('app.tagline').' Browse creators on YouTube, Instagram and X. Claimed profiles show numbers that come straight from the creator’s own account.';
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
    <meta name="theme-color" content="#0f3d2e">
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
<body class="min-h-full flex flex-col">
    {{-- With a hero band the header sits inside it; otherwise it is a plain white bar. --}}
    <header class="{{ isset($hero) ? 'bg-band' : 'border-b border-ink-200 bg-white' }}">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between gap-6">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="display text-[17px] tracking-[-0.02em] flex items-center gap-2.5 whitespace-nowrap">
                    @if($hasLogo)<img src="/logo.png" alt="" class="size-6 shrink-0">@else<span class="inline-flex size-6 items-center justify-center rounded-[7px] bg-brand-700"><svg class="size-3.5 text-white" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg></span>@endif
                    {{ config('app.name') }}
                </a>
                <nav class="hidden sm:flex items-center gap-6 text-sm font-medium text-ink-700">
                    <a href="{{ route('creators.index') }}" class="hover:text-ink-950 {{ request()->routeIs('creators.index') ? 'text-ink-950' : '' }}">Browse creators</a>
                    <a href="{{ route('about') }}" class="hover:text-ink-950 {{ request()->routeIs('about') ? 'text-ink-950' : '' }}">About</a>
                </nav>
            </div>
            <nav class="flex items-center gap-4 text-sm text-ink-700 whitespace-nowrap">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-ink-950 {{ request()->routeIs('admin.*') ? 'text-ink-950 font-medium' : '' }}">Admin</a>
                    @endif
                    <a href="{{ route('account') }}" class="hover:text-ink-950 {{ request()->routeIs('account*') ? 'text-ink-950 font-medium' : '' }}">My profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:text-ink-950">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-ink-950">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm !px-4 !py-2">Claim your profile</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
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
                        @if($hasLogo)<img src="/logo.png" alt="" class="size-6 shrink-0">@else<span class="inline-block size-5 rounded-md bg-brand-700"></span>@endif
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
                        @foreach(\App\Enums\Platform::cases() as $platform)
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
