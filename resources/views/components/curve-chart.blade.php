@props([
    'points',            // array of numbers, y values 0..100
    'labels' => [],      // x-axis labels: [[position 0..1, text], ...]
    'markers' => [],     // [[index, text], ...] points to call out
    'height' => 190,
])
@php
    $w = 520; $h = $height; $left = 40; $top = 16; $bottom = $h - 40; $right = $w - 10;
    $n = count($points);
    $x = fn (int $i) => $n > 1 ? $left + ($right - $left) * $i / ($n - 1) : $left;
    $y = fn ($v) => $bottom - ($bottom - $top) * max(0, min(100, $v)) / 100;
    $path = collect($points)->map(fn ($v, $i) => ($i === 0 ? 'M' : 'L').round($x($i), 1).' '.round($y($v), 1))->implode(' ');
    $area = $path.' L'.round($right, 1).' '.$bottom.' L'.$left.' '.$bottom.' Z';
@endphp
<svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="{{ $h }}" class="block" role="img" {{ $attributes }}>
    <line x1="{{ $left }}" y1="{{ $top }}" x2="{{ $left }}" y2="{{ $bottom }}" stroke="#dfe8e2"></line>
    <line x1="{{ $left }}" y1="{{ $bottom }}" x2="{{ $right }}" y2="{{ $bottom }}" stroke="#dfe8e2"></line>
    <line x1="{{ $left }}" y1="{{ ($top + $bottom) / 2 }}" x2="{{ $right }}" y2="{{ ($top + $bottom) / 2 }}" stroke="#edf3ef"></line>
    <line x1="{{ $left }}" y1="{{ $top }}" x2="{{ $right }}" y2="{{ $top }}" stroke="#edf3ef"></line>
    <text x="{{ $left - 6 }}" y="{{ $top + 4 }}" font-size="10" fill="#a1a1aa" text-anchor="end">100%</text>
    <text x="{{ $left - 6 }}" y="{{ ($top + $bottom) / 2 + 4 }}" font-size="10" fill="#a1a1aa" text-anchor="end">50%</text>
    <text x="{{ $left - 6 }}" y="{{ $bottom + 4 }}" font-size="10" fill="#a1a1aa" text-anchor="end">0%</text>
    <path d="{{ $area }}" fill="#059669" fill-opacity="0.10"></path>
    <path d="{{ $path }}" fill="none" stroke="#059669" stroke-width="2" stroke-linejoin="round"></path>
    @foreach($markers as [$index, $text])
        @php($anchor = $index > $n * 0.7 ? 'end' : ($index < $n * 0.15 ? 'start' : 'middle'))
        <line x1="{{ round($x($index), 1) }}" y1="{{ $top }}" x2="{{ round($x($index), 1) }}" y2="{{ $bottom }}" stroke="#c4e0d0" stroke-dasharray="3 3"></line>
        <circle cx="{{ round($x($index), 1) }}" cy="{{ round($y($points[$index]), 1) }}" r="3" fill="#059669"></circle>
        <text x="{{ round($x($index) + ($anchor === 'end' ? -6 : ($anchor === 'start' ? 6 : 0)), 1) }}" y="{{ round($y($points[$index]) - 8, 1) }}" font-size="11" font-weight="600" fill="#18181b" text-anchor="{{ $anchor }}">{{ $text }}</text>
    @endforeach
    @foreach($labels as [$pos, $text])
        @php($anchor = $pos >= 0.99 ? 'end' : ($pos <= 0.01 ? 'start' : 'middle'))
        <text x="{{ round($left + ($right - $left) * $pos, 1) }}" y="{{ $bottom + 18 }}" font-size="10" fill="#71717a" text-anchor="{{ $anchor }}">{{ $text }}</text>
    @endforeach
</svg>
