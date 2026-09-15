@props(['label', 'value', 'hint' => null])
<div {{ $attributes->class(['min-w-0']) }}>
    <p class="text-xs text-ink-500 leading-tight">{{ $label }}</p>
    <p class="mt-1 text-xl font-semibold tracking-tight text-ink-950 tnum leading-none">{{ $value }}</p>
    @if($hint)
        <p class="mt-1 text-[11px] leading-snug text-ink-400">{{ $hint }}</p>
    @endif
</div>
