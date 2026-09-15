@php
    use App\Enums\ContentType;
    use App\Support\Format;
    $isReel = $type === ContentType::Reel;
    $avgLength = $i->averageLength();
@endphp
<div class="card grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 divide-x divide-ink-100">
    <x-stat class="p-3.5" label="Median views" :value="Format::compact($p->median_views)" hint="a typical {{ $type->singular() }}" />
    <x-stat class="p-3.5" label="Average views" :value="Format::compact($p->average_views)" hint="all {{ $type->label() }}, viral ones included" />
    <x-stat class="p-3.5" label="Median reach" :value="Format::compact($p->median_reach)" hint="unique people, a typical {{ $type->singular() }}" />
    <x-stat class="p-3.5" label="New viewers" :value="Format::percent($i->nonFollowerShare(), 0)" hint="people reached who don’t follow yet" />
    @if($isReel)
        <x-stat class="p-3.5" label="Average watch time" :value="Format::duration($p->average_watch_time)" :hint="$avgLength ? 'of a '.Format::duration($avgLength).' Reel'.($i->watchShare() ? ' · '.round($i->watchShare()).'%' : '') : null" />
    @else
        <x-stat class="p-3.5" label="Profile visits" :value="Format::compact($p->extra('median_profile_visits'))" hint="a typical post" />
    @endif
    <x-stat class="p-3.5" label="Save rate" :value="Format::percent($i->saveRate())" hint="saves per person reached" />
    <x-stat class="p-3.5" label="Engagement" :value="Format::percent($p->engagement_rate)" hint="likes, comments, saves, shares per view" />
    <x-stat class="p-3.5" label="{{ ucfirst($type->label()) }} per month" :value="$p->extra('posts_per_month') !== null ? number_format($p->extra('posts_per_month'), 1) : '—'" :hint="$p->extra('longest_gap_days') !== null ? 'longest gap '.$p->extra('longest_gap_days').' days' : null" />
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-2">
    <x-section-card title="Who each {{ $type->singular() }} reaches: followers vs. new people" subtitle="Last {{ $contents->count() }} {{ $type->label() }}, oldest to newest. New people are what a brand pays for when the goal is awareness.">
        @if($i->series('reach') && $i->followerReachSeries())
            <x-bar-strip :values="$i->series('reach')" :stacked="$i->followerReachSeries()" :height="150" :cap="$p->median_reach ? $p->median_reach * 3 : null" />
            <p class="flex flex-wrap gap-4 text-xs text-ink-500 tnum">
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand-200"></span>Followers · about {{ Format::percent($i->followerShare(), 0) }} of reach</span>
                <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-brand-600"></span>New people · about {{ Format::percent($i->nonFollowerShare(), 0) }}</span>
                <span class="ml-auto">Split from Instagram’s 30-day reach breakdown · dashed top = cut off, a viral one</span>
            </p>
        @elseif($i->series('reach'))
            <x-bar-strip :values="$i->series('reach')" :height="150" />
            <p class="text-xs text-ink-500">Reach per {{ $type->singular() }}. Instagram hasn’t shared the follower split for this account.</p>
        @else
            <p class="text-sm text-ink-500">No reach data yet.</p>
        @endif
    </x-section-card>

    @if($isReel)
        <x-section-card title="How much of each Reel gets watched" subtitle="Average watch time as a share of the Reel’s length. Instagram doesn’t share second-by-second data, so this is the closest verified number.">
            <x-bar-strip :values="$i->watchShareSeries()" :height="150" />
            <dl class="grid grid-cols-3 gap-2 text-xs tnum">
                <div><dt class="text-ink-500">Typically watched</dt><dd class="font-semibold">{{ $i->watchShare() ? round($i->watchShare()).'% of length' : '—' }}</dd></div>
                <div><dt class="text-ink-500">Average Reel length</dt><dd class="font-semibold">{{ Format::duration($avgLength) }}</dd></div>
                <div><dt class="text-ink-500">Total watch time, {{ $contents->count() }} Reels</dt><dd class="font-semibold">{{ $p->extra('average_watch_time_seconds') !== null ? Format::compact(round($p->extra('average_watch_time_seconds') * $p->sample_size / 3600)).' hours' : '—' }}</dd></div>
            </dl>
        </x-section-card>
    @else
        <x-section-card title="Views per post" subtitle="Last {{ $contents->count() }} posts, oldest to newest.">
            <x-bar-strip :values="$i->series('views')" :height="150" :cap="$p->median_views * 3" />
        </x-section-card>
    @endif
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <x-section-card title="Who follows" subtitle="Who follows, from Instagram.">
        @if($audience?->age)
            <x-distribution :rows="$audience->top('age', 6)" />
            @if($i->genderSummary())<p class="border-t border-ink-100 pt-3 text-xs tnum"><span class="text-ink-500">Gender</span><br><span class="font-semibold">{{ $i->genderSummary() }}</span></p>@endif
        @else
            <p class="text-sm text-ink-500">Instagram only shares audience details for accounts with at least 100 followers.</p>
        @endif
    </x-section-card>

    <x-section-card title="Where they are" subtitle="Followers by country and top cities.">
        @if($i->topCountries())
            <x-distribution :rows="$i->topCountries()" />
            @if($i->citySummary())<p class="border-t border-ink-100 pt-3 text-xs tnum"><span class="text-ink-500">Top cities</span><br><span class="font-semibold">{{ $i->citySummary() }}</span></p>@endif
        @else
            <p class="text-sm text-ink-500">No country breakdown yet.</p>
        @endif
    </x-section-card>

    <x-section-card title="Reactions per 1,000 views" subtitle="Median {{ $type->singular() }}. Saves and shares travel further than likes.">
        <dl class="grid grid-cols-4 gap-2 tnum">
            <div><dt class="text-xs text-ink-500">Likes</dt><dd class="text-xl font-semibold">{{ $p->extra('likes_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Comments</dt><dd class="text-xl font-semibold">{{ $p->extra('comments_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Saves</dt><dd class="text-xl font-semibold">{{ $p->extra('saves_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Shares</dt><dd class="text-xl font-semibold">{{ $p->extra('shares_per_1k') ?? '—' }}</dd></div>
        </dl>
        <dl class="border-t border-ink-100 pt-3 space-y-1.5 text-xs tnum">
            @if($p->extra('median_profile_visits') !== null)<div class="flex justify-between"><dt class="text-ink-500">Profile visits per {{ $type->singular() }} (median)</dt><dd class="font-semibold">{{ number_format($p->extra('median_profile_visits')) }}</dd></div>@endif
            @if($p->extra('median_follows') !== null)<div class="flex justify-between"><dt class="text-ink-500">New follows per {{ $type->singular() }} (median)</dt><dd class="font-semibold">{{ number_format($p->extra('median_follows')) }}</dd></div>@endif
            @if($audience?->metric('profile_views_30d'))<div class="flex justify-between"><dt class="text-ink-500">Profile visits, 30 days</dt><dd class="font-semibold">{{ Format::compact($audience->metric('profile_views_30d')) }}</dd></div>@endif
        </dl>
    </x-section-card>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-[1fr_300px] lg:items-start">
    @include('creators.partials.profile.content-table', [
        'columns' => array_filter([
            'Reach' => 'reach',
            'Watch time' => $isReel ? fn ($c) => Format::duration($c->metric('average_watch_time_seconds')) : null,
            'Saves' => 'saves',
            'Shares' => 'shares',
        ]),
        'sparkline' => false,
    ])
    @include('creators.partials.profile.sidebar', ['totals' => array_filter([
        'People reached, 30 days' => Format::compact($audience?->metric('reach_30d')),
        'Profile visits, 30 days' => Format::compact($audience?->metric('profile_views_30d')),
        'Website clicks, 30 days' => Format::compact($audience?->metric('website_clicks_30d')),
        'Posts' => $i->account->public_data['media_count'] ?? null,
    ], fn ($v) => $v !== null && $v !== '—')])
</div>
