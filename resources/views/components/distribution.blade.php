@props(['rows', 'labels' => []])
{{-- rows: label => percentage --}}
<div {{ $attributes->class(['space-y-1.5 tnum']) }}>
    @foreach($rows as $label => $pct)
        <div class="flex items-center gap-2.5 text-[13px]">
            <span class="w-28 shrink-0 text-ink-700 truncate">{{ $labels[$label] ?? $label }}</span>
            <div class="flex-1 h-2 rounded-sm bg-ink-100 overflow-hidden"><div class="h-full bg-brand-600" style="width: {{ min(100, $pct) }}%"></div></div>
            <span class="w-10 shrink-0 text-right font-medium text-ink-950">{{ round($pct) }}%</span>
        </div>
    @endforeach
</div>
