@props(['creator'])
<div class="flex flex-wrap items-center justify-between gap-3 mb-6 border-b border-ink-200 pb-4">
    <div class="flex items-center gap-3">
        <x-avatar :creator="$creator" size="md" />
        <div>
            <p class="font-semibold text-ink-950">{{ $creator->name }}</p>
            <a href="{{ route('creators.show', $creator) }}" class="text-xs text-ink-500 hover:text-ink-950">View public profile →</a>
        </div>
    </div>
    <nav class="flex gap-1 text-sm">
        @foreach([['account', 'Profile'], ['account.connections', 'Connected accounts'], ['account.requests', 'Contact requests']] as [$route, $label])
            <a href="{{ route($route) }}" class="rounded-md px-3 py-1.5 {{ request()->routeIs($route) ? 'bg-brand-600 text-white' : 'text-ink-700 hover:bg-ink-100' }}">{{ $label }}</a>
        @endforeach
    </nav>
</div>
