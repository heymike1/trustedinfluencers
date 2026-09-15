@props(['values', 'height' => 120, 'highlight' => [], 'gap' => 'gap-1', 'stacked' => null, 'cap' => null])
{{-- cap: clip the scale so one viral item doesn't flatten the rest; clipped bars get a dashed top --}}
@php($max = max(1, $cap ?? max($values ?: [1])))
<div {{ $attributes->class(["flex items-end $gap border-b border-ink-200"]) }} style="height: {{ $height }}px">
    @foreach($values as $i => $v)
        @php($clipped = $v > $max)
        @if($stacked)
            <div class="flex-1 flex flex-col justify-end h-full {{ $clipped ? 'border-t-2 border-dashed border-ink-400' : '' }}" title="{{ number_format($v) }}">
                <div class="bg-brand-600 rounded-t-sm" style="height: {{ round(min($v, $max) * max(0, ($v - $stacked[$i])) / max(1, $v) / $max * 100, 1) }}%"></div>
                <div class="bg-brand-200" style="height: {{ round(min($v, $max) * $stacked[$i] / max(1, $v) / $max * 100, 1) }}%"></div>
            </div>
        @else
            <div class="flex-1 rounded-t-sm {{ in_array($i, $highlight) ? 'bg-ink-400' : 'bg-brand-600' }} {{ $clipped ? 'border-t-2 border-dashed border-ink-400' : '' }}" style="height: {{ round(min($v, $max) / $max * 100, 1) }}%" title="{{ number_format($v) }}"></div>
        @endif
    @endforeach
</div>
