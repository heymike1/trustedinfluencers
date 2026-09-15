<section id="top-performers" class="scroll-mt-6">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-3">
        <div>
            <h2 class="text-base font-semibold text-ink-950">Top performing creators</h2>
            <p class="mt-0.5 text-xs text-ink-500">Verified profiles only. Sort by what matters for your campaign. The order changes a lot.</p>
        </div>
        <div class="flex gap-1.5">
            <button type="button" wire:click="$set('platform', '')" class="chip {{ $platform === '' ? 'chip-on' : '' }}">All</button>
            @foreach($platforms as $p)
                <button type="button" wire:click="$set('platform', '{{ $p->value }}')" class="chip {{ $platform === $p->value ? 'chip-on' : '' }}"><x-platform-icon :platform="$p" class="size-3.5" :colored="$platform !== $p->value" />{{ $p->label() }}</button>
            @endforeach
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-1.5 mb-3 text-xs">
        <span class="text-ink-500 mr-1">Rank by</span>
        @foreach($metrics as $m)
            <button type="button" wire:click="$set('metric', '{{ $m->value }}')" class="chip {{ $active === $m ? 'chip-on' : '' }}">{{ $m->label() }}</button>
        @endforeach
        <select wire:model.live="category" class="input w-auto py-1.5 text-xs ml-auto">
            <option value="">Any category</option>
            @foreach($categories as $c)
                <option value="{{ $c->slug }}">{{ $c->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="card overflow-x-auto" wire:loading.class="opacity-60">
        <table class="data-table tnum">
            <thead>
                <tr>
                    <th class="w-8 pl-4">#</th>
                    <th>Creator</th>
                    <th class="text-right">Audience</th>
                    <th class="text-right text-ink-950">{{ $active->shortLabel() }} ↓</th>
                    @if($platform === 'youtube')<th>Watch curve</th>@endif
                    @if($active !== \App\Enums\RankMetric::MedianViews)<th class="text-right">Median views</th>@endif
                    @if($active !== \App\Enums\RankMetric::Engagement)<th class="text-right">Engagement</th>@endif
                    <th>Top country · age</th>
                    <th class="pr-4"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($creators as $i => $creator)
                    @php
                        $account = $creator->primaryAccount();
                        $perf = $account?->performanceFor($account->platform->primaryContentType());
                        $audienceLine = \App\View\AudienceSummary::line($account?->audience);
                    @endphp
                    <tr wire:key="top-{{ $creator->id }}">
                        <td class="pl-4 font-semibold {{ $i < 3 ? 'text-brand-700' : 'text-ink-500' }}">{{ $i + 1 }}</td>
                        <td>
                            <a href="{{ route('creators.show', $creator) }}" class="flex items-center gap-2.5 min-w-0">
                                <x-avatar :creator="$creator" size="sm" />
                                <span class="min-w-0">
                                    <span class="block font-semibold text-ink-950 truncate leading-[18px]">{{ $creator->name }}</span>
                                    <span class="flex items-center gap-1 text-xs text-ink-500 truncate">@if($account)<x-platform-icon :platform="$account->platform" class="size-3 shrink-0" :colored="true" /> {{ $account->handleWithAt() }}@endif @if($creator->category)<span class="text-ink-300">·</span> {{ $creator->category->name }}@endif</span>
                                </span>
                            </a>
                        </td>
                        <td class="text-right text-ink-700 whitespace-nowrap">{{ \App\Support\Format::compact($account?->follower_count) }} <span class="text-ink-400 text-xs">{{ $account ? $account->platform->audienceNoun() : '' }}</span></td>
                        <td class="text-right font-semibold text-ink-950">{{ $active->format($active->value($creator)) }}</td>
                        @if($platform === 'youtube')
                            <td>@if($perf?->extra('retention_curve'))<x-sparkline :points="$perf->extra('retention_curve')" />@else<span class="text-ink-300">—</span>@endif</td>
                        @endif
                        @if($active !== \App\Enums\RankMetric::MedianViews)<td class="text-right text-ink-700">{{ \App\Support\Format::compact($creator->median_views) }}</td>@endif
                        @if($active !== \App\Enums\RankMetric::Engagement)<td class="text-right text-ink-700">{{ \App\Support\Format::percent($creator->engagement_rate) }}</td>@endif
                        <td class="text-xs text-ink-700 whitespace-nowrap">{{ $audienceLine ?? '—' }}</td>
                        <td class="pr-4 text-right"><x-badge variant="verified">Verified</x-badge></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-ink-500 py-8">No verified creators match this yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2.5 border-t border-ink-200 bg-ink-50 text-xs text-ink-500">
            <span>{{ $active->explanation() }}</span>
            <a href="{{ route('creators.index', array_filter(['platform' => $platform, 'category' => $category, 'verified' => 1, 'sort' => $active === \App\Enums\RankMetric::Engagement ? 'engagement' : 'median_views'])) }}" class="font-medium text-ink-900 hover:underline whitespace-nowrap">All {{ number_format($total) }} verified creators →</a>
        </div>
    </div>
</section>
