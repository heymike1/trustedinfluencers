@props(['state'])
@php($state = $state instanceof \App\Enums\ProfileState ? $state : \App\Enums\ProfileState::from($state))
@switch($state)
    @case(\App\Enums\ProfileState::VerifiedMetrics)
        <x-badge variant="verified" {{ $attributes }}>
            <svg class="size-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.6l7.3-7.3a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
            Verified metrics
        </x-badge>
        @break
    @case(\App\Enums\ProfileState::MetricsOutdated)
        <x-badge variant="warn" {{ $attributes }}>Metrics outdated</x-badge>
        @break
    @case(\App\Enums\ProfileState::NeedsReconnection)
        <x-badge variant="warn" {{ $attributes }}>Needs reconnection</x-badge>
        @break
    @case(\App\Enums\ProfileState::Claimed)
        <x-badge {{ $attributes }}>Claimed</x-badge>
        @break
    @default
        <x-badge {{ $attributes }}>Unclaimed</x-badge>
@endswitch
