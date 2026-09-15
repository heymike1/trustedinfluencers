@props(['points', 'width' => 72, 'height' => 20, 'color' => '#1d6ef5'])
@php
    $n = count($points);
    $max = max(1, max($points)); $min = min($points);
    $range = max(1e-6, $max - $min);
    $path = collect($points)->map(fn ($v, $i) => ($i === 0 ? 'M' : 'L').round($n > 1 ? $i / ($n - 1) * $width : 0, 1).' '.round($height - 1 - ($v - $min) / $range * ($height - 2), 1))->implode(' ');
@endphp
<svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 {{ $width }} {{ $height }}" class="inline-block align-middle" aria-hidden="true"><path d="{{ $path }}" fill="none" stroke="{{ $color }}" stroke-width="1.5" stroke-linejoin="round"></path></svg>
