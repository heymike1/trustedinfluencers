@props(['creator', 'size' => 'md'])
@php
    $classes = match($size) {
        'xs' => 'size-6 text-[10px]',
        'sm' => 'size-8 text-xs',
        'md' => 'size-11 text-sm',
        'lg' => 'size-16 text-lg',
        'xl' => 'size-20 text-xl',
        default => 'size-11 text-sm',
    };
    $hue = crc32($creator->slug) % 360;
@endphp
<span {{ $attributes->class(["relative inline-flex shrink-0 items-center justify-center rounded-full overflow-hidden font-semibold select-none $classes"]) }}
      style="background-color: hsl({{ $hue }} 30% 92%); color: hsl({{ $hue }} 35% 30%)">
    <span>{{ $creator->initials() }}</span>
    @if($creator->avatar_url)
        <img src="{{ $creator->avatar_url }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" onerror="this.remove()">
    @endif
</span>
