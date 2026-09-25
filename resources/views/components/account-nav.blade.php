@props(['creator'])
@php
    $connected = $creator->socialAccounts->filter->hasVerifiedMetrics();
    $unread = $creator->contactRequests()->whereNull('read_at')->count();
    $tabs = [
        ['account', 'Overview', null],
        ['account.profile', 'Edit profile', null],
        ['account.connections', 'Connected accounts', null],
        ['account.requests', 'Messages', $unread],
    ];
@endphp
<x-page-band class="!mb-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <x-avatar :creator="$creator" size="lg" />
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="display text-2xl sm:text-[26px] tracking-[-0.025em]">{{ $creator->name }}</h1>
                    @if($connected->isNotEmpty())
                        <x-badge variant="verified" class="gap-1.5"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified through @foreach($connected as $a)<x-platform-icon :platform="$a->platform" class="size-3" :colored="true" /> {{ $a->platform->label() }}@if(! $loop->last) · @endif @endforeach</x-badge>
                    @elseif(! $creator->is_listed)
                        <x-badge variant="warn">Hidden from directory</x-badge>
                    @endif
                </div>
                <p class="mt-0.5 text-[13.5px] text-ink-500">
                    @if($primary = $creator->primaryAccount()){{ $primary->handleWithAt() }} · @endif
                    @if($creator->category){{ $creator->category->name }} · @endif
                    {{ $creator->is_listed ? 'Profile is listed in the directory' : 'Profile is hidden from the directory' }}
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('account.login') }}" class="btn-secondary border-band-edge {{ request()->routeIs('account.login') ? '!border-brand-700 !bg-brand-700 !text-white' : '' }}">
                <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="7" r="3"/><path d="M4 17c0-3 2.7-5 6-5s6 2 6 5"/></svg>
                Manage my account
            </a>
            <a href="{{ route('creators.show', $creator) }}" class="btn-secondary border-band-edge">View public profile <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
        </div>
    </div>
    <nav class="mt-5 flex flex-wrap gap-1 text-sm">
        @foreach($tabs as [$route, $label, $count])
            @php($on = request()->routeIs($route))
            <a href="{{ route($route) }}" class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 {{ $on ? 'bg-brand-700 font-semibold text-white' : 'font-medium text-ink-700 hover:bg-white/70' }}">
                {{ $label }}
                @if($count)<span class="inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full px-1.5 text-[11px] font-bold tnum {{ $on ? 'bg-white text-brand-700' : 'bg-brand-700 text-white' }}">{{ $count }}</span>@endif
            </a>
        @endforeach
    </nav>
</x-page-band>
