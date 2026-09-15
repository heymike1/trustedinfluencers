<section id="leaderboard" class="scroll-mt-6 card p-2.5 !rounded-[22px]">
    <div class="rounded-[14px] border border-ink-200 bg-white overflow-hidden" wire:loading.class="opacity-60">
        <div class="flex flex-wrap items-end justify-between gap-3 px-5 pt-5 pb-3">
            <div>
                <h2 class="display text-xl tracking-[-0.02em]">Leaderboard</h2>
                <p class="mt-0.5 text-[13px] text-ink-500">Verified profiles only. Rank by what matters for your campaign; the order changes a lot.</p>
            </div>
            <div class="flex gap-1.5">
                <button type="button" wire:click="$set('platform', '')" class="chip !py-2 {{ $platform === '' ? 'chip-on' : '' }}">All</button>
                @foreach($platforms as $p)
                    <button type="button" wire:click="$set('platform', '{{ $p->value }}')" class="chip !py-2 {{ $platform === $p->value ? 'chip-on' : '' }}"><x-platform-icon :platform="$p" class="size-3.5" :colored="$platform !== $p->value" />{{ $p->label() }}</button>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-1.5 px-5 pb-4 text-xs">
            <span class="text-ink-500 mr-1">Rank by</span>
            @foreach($metrics as $m)
                <button type="button" wire:click="$set('metric', '{{ $m->value }}')" class="chip {{ $active === $m ? 'chip-on' : '' }}">{{ $m->label() }}</button>
            @endforeach
            <select wire:model.live="category" class="input w-auto rounded-full py-1.5 pl-3 pr-8 text-xs ml-auto">
                <option value="">Any category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->slug }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table tnum">
                <thead class="bg-ink-50">
                    <tr>
                        <th class="w-10 pl-5">#</th>
                        <th>Creator</th>
                        <th class="text-right">Audience</th>
                        <th class="text-right text-ink-950">{{ $active->shortLabel() }} ↓</th>
                        @if($platform === 'youtube')<th>Watch curve</th>@endif
                        @if($active !== \App\Enums\RankMetric::MedianViews)<th class="text-right">Median views</th>@endif
                        @if($active !== \App\Enums\RankMetric::Engagement)<th class="text-right">Engagement</th>@endif
                        <th>Top country · age</th>
                        <th class="pr-5"></th>
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
                            <td class="pl-5 font-bold {{ $i < 3 ? 'text-brand-700' : 'text-ink-500 font-semibold' }}">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('creators.show', $creator) }}" class="flex items-center gap-3 min-w-0 py-0.5">
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
                                <td>@if($perf?->extra('retention_curve'))<x-sparkline :points="$perf->extra('retention_curve')" :width="96" :height="26" />@else<span class="text-ink-300">—</span>@endif</td>
                            @endif
                            @if($active !== \App\Enums\RankMetric::MedianViews)<td class="text-right text-ink-700">{{ \App\Support\Format::compact($creator->median_views) }}</td>@endif
                            @if($active !== \App\Enums\RankMetric::Engagement)<td class="text-right text-ink-700">{{ \App\Support\Format::percent($creator->engagement_rate) }}</td>@endif
                            <td class="text-xs text-ink-500 whitespace-nowrap">{{ $audienceLine ?? '—' }}</td>
                            <td class="pr-5 text-right"><x-badge variant="verified"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified</x-badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-ink-500 py-8">No verified creators match this yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 border-t border-ink-100 bg-ink-50 text-[13px] text-ink-500">
            <span>{{ $active->explanation() }}</span>
            <a href="{{ route('creators.index', array_filter(['platform' => $platform, 'category' => $category, 'verified' => 1, 'sort' => $active === \App\Enums\RankMetric::Engagement ? 'engagement' : 'median_views'])) }}" class="inline-flex items-center gap-1.5 font-semibold text-brand-700 hover:underline whitespace-nowrap">All {{ number_format($total) }} verified creators <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
        </div>
    </div>
</section>
