<div class="flex flex-wrap items-center justify-between gap-3 mb-6 border-b border-ink-200 pb-4">
    <h1 class="text-xl font-semibold tracking-tight text-ink-950">Admin</h1>
    <nav class="flex flex-wrap gap-1 text-sm">
        @foreach([['admin.dashboard', 'Overview'], ['admin.creators', 'Creators'], ['admin.users', 'Users'], ['admin.claims', 'Claims'], ['admin.duplicates', 'Duplicates'], ['admin.connections', 'Connections'], ['admin.sponsors', 'Sponsors']] as [$route, $label])
            <a href="{{ route($route) }}" class="rounded-full px-3.5 py-1.5 {{ request()->routeIs($route) || ($route === 'admin.creators' && request()->routeIs('admin.creators.*')) ? 'bg-brand-700 text-white' : 'text-ink-700 hover:bg-ink-100' }}">{{ $label }}</a>
        @endforeach
    </nav>
</div>
