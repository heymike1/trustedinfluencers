@props(['variant' => 'neutral'])
@php($classes = match($variant) {
    'verified' => 'border-verified-100 bg-verified-50 text-verified-600',
    'warn' => 'border-warn-100 bg-amber-50 text-warn-700',
    'danger' => 'border-danger-100 bg-red-50 text-danger-700',
    'dark' => 'border-ink-900 bg-ink-900 text-white',
    default => 'border-ink-200 bg-ink-50 text-ink-700',
})
<span {{ $attributes->class(["inline-flex items-center gap-1 rounded border px-1.5 py-0.5 text-[11px] font-medium leading-4 whitespace-nowrap $classes"]) }}>{{ $slot }}</span>
