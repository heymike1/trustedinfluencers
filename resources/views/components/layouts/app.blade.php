@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'noindex' => false,
    'wide' => false,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name').' · '.config('app.tagline') }}</title>
    @if($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @if($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    @if($noindex)
        <meta name="robots" content="noindex">
    @endif
    <meta property="og:title" content="{{ $title ?? config('app.name').' · '.config('app.tagline') }}">
    @if($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col">
    <header class="border-b border-ink-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-14 flex items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="font-semibold tracking-tight text-ink-950 flex items-center gap-2 whitespace-nowrap">
                    <span class="inline-block size-5 rounded bg-brand-600"></span>
                    {{ config('app.name') }}
                </a>
                <nav class="hidden sm:flex items-center gap-5 text-sm text-ink-700">
                    <a href="{{ route('creators.index') }}" class="hover:text-ink-950 {{ request()->routeIs('creators.index') ? 'text-ink-950 font-medium' : '' }}">Browse creators</a>
                    <a href="{{ route('home') }}#top-performers" class="hover:text-ink-950">Top performers</a>
                    <a href="{{ route('creators.create') }}" class="hover:text-ink-950 {{ request()->routeIs('creators.create') ? 'text-ink-950 font-medium' : '' }}">Add a creator</a>
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
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Claim your profile</a>
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
                        <span class="inline-block size-5 rounded bg-brand-600"></span>
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
                        <li><a href="{{ route('home') }}#top-performers" class="hover:text-ink-950">Top performers</a></li>
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
                <p>© {{ date('Y') }} {{ config('app.name') }}</p>
                <p>Not affiliated with YouTube, Instagram or X. Platform names and icons belong to their owners.</p>
            </div>
        </div>
    </footer>
</body>
</html>
