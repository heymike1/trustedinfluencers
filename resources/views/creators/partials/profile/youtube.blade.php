@php
    use App\Support\Format;
    $curve = $i->retentionCurve();
    $velocity = $i->velocityCurve();
    $avgLength = $i->averageLength();
    $cadence = $i->cadenceByMonth();
@endphp
<div class="card grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 divide-x divide-ink-100">
    <x-stat class="p-3.5" label="Median views" :value="Format::compact($p->median_views)" hint="a typical video" />
    <x-stat class="p-3.5" label="Average views" :value="Format::compact($p->average_views)" hint="all videos, viral ones included" />
    <x-stat class="p-3.5" label="Views in first 7 days" :value="Format::compact($p->extra('median_views_7d'))" hint="median" />
    <x-stat class="p-3.5" label="Still watching halfway" :value="Format::percent($curve[10] ?? null, 0)" hint="good spot for a mid-video mention" />
    <x-stat class="p-3.5" label="Average watch time" :value="Format::duration($p->average_watch_time)" :hint="$avgLength ? 'of '.Format::duration($avgLength).' avg length' : null" />
    <x-stat class="p-3.5" label="Engagement" :value="Format::percent($p->engagement_rate)" hint="likes, comments, shares per view" />
    <x-stat class="p-3.5" label="Views vs. subscribers" :value="Format::percent($i->viewsVsAudience(), 0)" hint="how many subscribers actually watch" />
    <x-stat class="p-3.5" label="Videos per month" :value="$p->extra('posts_per_month') !== null ? number_format($p->extra('posts_per_month'), 1) : '—'" :hint="$p->extra('longest_gap_days') !== null ? 'longest gap '.$p->extra('longest_gap_days').' days' : null" />
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-2">
    <x-section-card title="How much of a video people watch" subtitle="Of everyone who presses play, how many are still watching at each point. Averaged over the last {{ $p->sample_size }} videos.">
        @if($curve)
            <x-curve-chart :points="$curve" :labels="[[0, '0:00'], [0.5, $avgLength ? Format::duration($avgLength / 2) : '50%'], [1, $avgLength ? Format::duration($avgLength).' avg length' : '100%']]" :markers="[[1, Format::percent($curve[1], 0).' at 0:30'], [10, Format::percent($curve[10], 0).' at halfway'], [20, Format::percent($curve[20], 0).' at the end']]" />
            <dl class="grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 text-xs tnum">
                <div><dt class="text-ink-500">First 30 seconds</dt><dd class="font-semibold">100% → {{ Format::percent($curve[1], 0) }}</dd></div>
                <div><dt class="text-ink-500">Halfway</dt><dd class="font-semibold">{{ Format::percent($curve[10], 0) }} still watching</dd></div>
                <div><dt class="text-ink-500">The end</dt><dd class="font-semibold">{{ Format::percent($curve[20], 0) }} reach the end</dd></div>
            </dl>
        @else
            <p class="text-sm text-ink-500">Watch data hasn’t come in for these videos yet.</p>
        @endif
    </x-section-card>

    <x-section-card title="How fast the views come in" subtitle="How much of a video’s first-month views arrive each day. Shows when a sponsored mention has done most of its work.">
        @if($velocity)
            <x-curve-chart :points="$velocity" :labels="[[0, 'day 0'], [6/29, 'day 7'], [13/29, 'day 14'], [1, 'day 30']]" :markers="[[0, Format::percent($velocity[0], 0).' in 24h'], [6, Format::percent($velocity[6], 0).' in 7 days']]" />
            <dl class="grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 text-xs tnum">
                <div><dt class="text-ink-500">Median views, first day</dt><dd class="font-semibold">{{ Format::compact($p->extra('median_views_24h')) }}</dd></div>
                <div><dt class="text-ink-500">Median views, first week</dt><dd class="font-semibold">{{ Format::compact($p->extra('median_views_7d')) }}</dd></div>
                <div><dt class="text-ink-500">Videos in this curve</dt><dd class="font-semibold">Those older than 30 days</dd></div>
            </dl>
        @else
            <p class="text-sm text-ink-500">Needs videos that are at least 30 days old. Check back once the channel has some.</p>
        @endif
    </x-section-card>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <x-section-card title="Who watches" subtitle="Who watched over the last 90 days, from YouTube.">
        @if($audience?->age)
            <x-distribution :rows="$audience->top('age', 6)" />
            <div class="grid grid-cols-2 gap-3 border-t border-ink-100 pt-3 text-xs tnum">
                @if($i->genderSummary())<div><p class="text-ink-500">Gender</p><p class="font-semibold">{{ $i->genderSummary() }}</p></div>@endif
                @if($i->deviceSummary())<div><p class="text-ink-500">Device</p><p class="font-semibold">{{ $i->deviceSummary() }}</p></div>@endif
            </div>
        @else
            <p class="text-sm text-ink-500">YouTube hasn’t shared audience details for this channel yet.</p>
        @endif
    </x-section-card>

    <x-section-card title="Where they are" subtitle="Share of views by country, last 90 days.">
        @if($i->topCountries())
            <x-distribution :rows="$i->topCountries()" />
        @else
            <p class="text-sm text-ink-500">No country breakdown yet.</p>
        @endif
    </x-section-card>

    <x-section-card title="Reactions per 1,000 views" subtitle="Median video. Comments are the best sign people trust a recommendation.">
        <dl class="grid grid-cols-3 gap-2 tnum">
            <div><dt class="text-xs text-ink-500">Likes</dt><dd class="text-xl font-semibold">{{ $p->extra('likes_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Comments</dt><dd class="text-xl font-semibold">{{ $p->extra('comments_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Shares</dt><dd class="text-xl font-semibold">{{ $p->extra('shares_per_1k') ?? '—' }}</dd></div>
        </dl>
        @if($cadence->count() > 1)
            <div class="border-t border-ink-100 pt-3">
                <p class="text-xs text-ink-500 mb-1.5">Videos per month, recent uploads</p>
                <x-bar-strip :values="$cadence->values()->all()" :height="40" />
                <p class="mt-1 flex justify-between text-[11px] text-ink-500 tnum"><span>{{ \Carbon\Carbon::createFromFormat('Y-m', $cadence->keys()->first())->format('M Y') }}</span><span>{{ $cadence->sum() }} videos</span><span>{{ \Carbon\Carbon::createFromFormat('Y-m', $cadence->keys()->last())->format('M Y') }}</span></p>
            </div>
        @endif
    </x-section-card>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
    @include('creators.partials.profile.content-table', [
        'columns' => [
            'First week' => 'views_7d',
            'Watched' => fn ($c) => Format::percent($c->metric('average_view_percentage'), 0),
            'Watch time' => fn ($c) => Format::duration($c->metric('average_watch_time_seconds')),
            'Likes' => 'likes',
        ],
        'sparkline' => true,
    ])
    @include('creators.partials.profile.sidebar', ['totals' => array_filter([
        'Gained, last 28 days' => $audience?->metric('subscribers_gained_28d') !== null ? '+'.number_format($audience->metric('subscribers_gained_28d')) : null,
        'Views, last 28 days' => Format::compact($audience?->metric('views_28d')),
        'Watch time, last 28 days' => $audience?->metric('watch_time_seconds_28d') ? Format::compact(round($audience->metric('watch_time_seconds_28d') / 3600)).' hours' : null,
        'Total views' => Format::compact($i->account->public_data['total_views'] ?? null),
        'Videos' => $i->account->public_data['video_count'] ?? null,
    ], fn ($v) => $v !== null && $v !== '—')])
</div>
