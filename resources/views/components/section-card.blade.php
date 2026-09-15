@props(['title', 'subtitle' => null, 'verified' => true])
<div {{ $attributes->class(['card p-5 flex flex-col gap-3']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-[15px] font-semibold text-ink-950">{{ $title }}</h3>
            @if($subtitle)<p class="mt-0.5 text-xs text-ink-500">{{ $subtitle }}</p>@endif
        </div>
        @if($verified)<x-badge variant="verified">Verified</x-badge>@endif
    </div>
    {{ $slot }}
</div>
