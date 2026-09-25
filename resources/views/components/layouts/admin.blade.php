@props(['title' => null, 'heading' => null, 'subheading' => null])
@php($sections = [
    ['admin.dashboard', 'Overview', '<path d="M4 13h6V4H4v9zM4 20h6v-4H4v4zM14 20h6v-9h-6v9zM14 8h6V4h-6v4z"/>'],
    ['admin.creators', 'Creators', '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3 2.5-5 5.5-5s5.5 2 5.5 5M16 6h5M16 10h5M16 14h3"/>'],
    ['admin.accounts', 'Social accounts', '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M3 10h18M8 15h3"/>'],
    ['admin.content', 'Content', '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 9.5v5l4.5-2.5z"/>'],
    ['admin.claims', 'Claims', '<path d="M12 3l7 3v5c0 4.2-2.9 8-7 9-4.1-1-7-4.8-7-9V6z"/><path d="M9 12l2 2 4-4"/>'],
    ['admin.requests', 'Contact requests', '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>'],
    ['admin.categories', 'Categories', '<path d="M4 6h16M4 12h16M4 18h10"/>'],
    ['admin.users', 'Users', '<circle cx="9" cy="8" r="3.2"/><path d="M3 19c0-3 2.7-5 6-5s6 2 6 5"/><path d="M16 6.5a3 3 0 0 1 0 5.6M18 19c0-2-.7-3.5-2-4.5"/>'],
    ['admin.sponsors', 'Sponsors', '<rect x="3" y="6" width="7" height="12" rx="2"/><rect x="14" y="6" width="7" height="12" rx="2"/>'],
    ['admin.duplicates', 'Duplicates', '<rect x="4" y="4" width="11" height="11" rx="2"/><rect x="9" y="9" width="11" height="11" rx="2"/>'],
])
<x-layouts.bare :title="$title">
    <div class="flex min-h-dvh">
        {{-- The bar fills the window height and stays put while the table scrolls. --}}
        <aside class="hidden w-60 shrink-0 bg-brand-700 text-white lg:block">
            <div class="sticky top-0 flex h-dvh flex-col overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex h-16 items-center gap-2.5 px-5 text-white">
                <span class="inline-flex size-6 items-center justify-center rounded-[7px] bg-white/15"><svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg></span>
                <span class="display text-[15px] tracking-[-0.02em] !text-white">{{ config('app.name') }}</span>
            </a>
            <nav class="flex flex-1 flex-col gap-0.5 px-3 py-3">
                @foreach($sections as [$route, $label, $icon])
                    @php($on = request()->routeIs($route) || ($route === 'admin.creators' && request()->routeIs('admin.creators.*')))
                    <a href="{{ route($route) }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13.5px] font-medium {{ $on ? 'bg-white/15 text-white' : 'text-white/70 hover:bg-white/10 hover:text-white' }}">
                        <svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
                <div class="border-t border-white/10 px-3 py-3 text-[13px]">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
                        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        Back to the site
                    </a>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col bg-ink-50">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-200 bg-white px-4 py-3 sm:px-6">
                <div class="min-w-0">
                    <h1 class="display truncate text-xl tracking-[-0.025em]">{{ $heading ?? $title ?? 'Admin' }}</h1>
                    @if($subheading)<p class="mt-0.5 truncate text-[13px] text-ink-500">{{ $subheading }}</p>@endif
                </div>
                <div class="flex items-center gap-3">
                    {{ $actions ?? '' }}
                    <span class="hidden text-[13px] text-ink-500 sm:inline">{{ auth()->user()?->email }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm">Log out</button>
                    </form>
                </div>
            </header>

            {{-- The sidebar is hidden on small screens, so the sections live in a scroll strip instead. --}}
            <nav class="flex gap-1 overflow-x-auto border-b border-ink-200 bg-white px-4 py-2 lg:hidden">
                @foreach($sections as [$route, $label, $icon])
                    @php($on = request()->routeIs($route) || ($route === 'admin.creators' && request()->routeIs('admin.creators.*')))
                    <a href="{{ route($route) }}" class="whitespace-nowrap rounded-full px-3 py-1.5 text-[13px] font-medium {{ $on ? 'bg-brand-700 text-white' : 'text-ink-700 hover:bg-ink-100' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <main class="flex-1 px-4 py-5 sm:px-6">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.bare>
