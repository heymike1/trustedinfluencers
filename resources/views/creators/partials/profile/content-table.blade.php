@php
    use App\Support\Format;
    $viralId = $i->viralContentId();
@endphp
<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-ink-950">Last {{ $contents->count() }} {{ $contents->count() === 1 ? $type->singular() : $type->label() }}</h3>
        <p class="text-xs text-ink-500">Grey row = the viral one that lifts the average but not the median</p>
    </div>
    <div class="card overflow-x-auto">
        <table class="data-table tnum">
            <thead>
                <tr>
                    <th class="pl-4">{{ ucfirst($type->singular()) }}</th>
                    <th>Published</th>
                    <th class="text-right">Views</th>
                    @foreach($columns as $label => $key)<th class="text-right">{{ $label }}</th>@endforeach
                    @if($sparkline)<th class="pr-4">Watch curve</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($contents->take(8) as $content)
                    @php($m = $content->metrics ?? [])
                    @php($viral = $content->id === $viralId)
                    <tr class="{{ $viral ? 'bg-ink-50 text-ink-500' : '' }}">
                        <td class="pl-4">
                            <a href="{{ $content->url }}" target="_blank" rel="noopener nofollow" class="flex items-center gap-2.5 min-w-0 hover:underline">
                                <span class="shrink-0 overflow-hidden rounded bg-ink-100 {{ in_array($type, [\App\Enums\ContentType::Short, \App\Enums\ContentType::Reel]) ? 'w-6 h-10' : 'w-14 h-8' }}">@if($content->thumbnail_url)<img src="{{ $content->thumbnail_url }}" alt="" class="size-full object-cover" loading="lazy" onerror="this.remove()">@endif</span>
                                <span class="font-medium {{ $viral ? '' : 'text-ink-900' }} truncate max-w-[220px]">{{ $content->title ?: 'Untitled' }}</span>
                            </a>
                        </td>
                        <td class="text-ink-500 whitespace-nowrap">{{ $content->published_at?->format('j M') }}</td>
                        <td class="text-right font-semibold">{{ Format::compact($m['views'] ?? null) }}</td>
                        @foreach($columns as $label => $key)
                            <td class="text-right">{{ is_callable($key) ? $key($content) : Format::compact($m[$key] ?? null) }}</td>
                        @endforeach
                        @if($sparkline)<td class="pr-4">@if($content->retentionCurve())<x-sparkline :points="$content->retentionCurve()" :color="$viral ? '#a1a1aa' : '#059669'" />@else —@endif</td>@endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($contents->count() > 8)
            <p class="px-4 py-2.5 border-t border-ink-200 bg-ink-50 text-xs text-ink-500">Showing 8 of {{ $contents->count() }}. The numbers above use all {{ $contents->count() }}.</p>
        @endif
    </div>
</div>
