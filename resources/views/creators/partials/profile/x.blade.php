@php
    use App\Support\Format;
    $views = $i->series('views');
    $cap = $p->median_views * 3;
@endphp
<div class="card grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 divide-x divide-ink-100">
    <x-stat class="p-3.5" label="Median views" :value="Format::compact($p->median_impressions)" hint="a typical post (X calls these impressions)" />
    <x-stat class="p-3.5" label="Average views" :value="Format::compact($p->average_impressions)" hint="all posts, viral ones included" />
    <x-stat class="p-3.5" label="Engagement" :value="Format::percent($p->engagement_rate)" hint="likes, replies, reposts, bookmarks per view" />
    <x-stat class="p-3.5" label="Profile visits per post" :value="Format::compact($p->extra('median_profile_clicks'))" hint="median, last 30 days only" />
    <x-stat class="p-3.5" label="Link clicks per post" :value="Format::compact($p->extra('median_url_clicks'))" :hint="$i->linkClickRate() !== null ? number_format($i->linkClickRate(), 2).'% of views' : 'median, last 30 days only'" />
    <x-stat class="p-3.5" label="Views vs. followers" :value="Format::percent($i->viewsVsAudience(), 0)" hint="how many followers actually see a post" />
    <x-stat class="p-3.5" label="Posts per month" :value="$p->extra('posts_per_month') !== null ? number_format($p->extra('posts_per_month'), 1) : '—'" :hint="$p->extra('longest_gap_days') !== null ? 'longest gap '.$p->extra('longest_gap_days').' days' : null" />
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <x-section-card class="lg:col-span-2" title="Views per post" subtitle="Last {{ $contents->count() }} posts, oldest to newest. The line is the median.">
        <div class="relative">
            <x-bar-strip :values="$views" :height="150" :cap="$cap" />
            @if($views && max($views) > 0)
                <div class="absolute inset-x-0 border-t border-dashed border-ink-400" style="bottom: {{ round($p->median_views / min(max($views), $cap) * 100, 1) }}%"></div>
            @endif
        </div>
        <p class="text-xs text-ink-500 tnum">Median {{ Format::compact($p->median_views) }} · average {{ Format::compact($p->average_views) }} · best {{ Format::compact($p->extra('max_views')) }} · dashed top = cut off, a viral one</p>
    </x-section-card>

    <x-section-card title="Reactions per 1,000 views" subtitle="Median post. Replies are the best sign people trust a recommendation.">
        <dl class="grid grid-cols-2 gap-3 tnum">
            <div><dt class="text-xs text-ink-500">Likes</dt><dd class="text-xl font-semibold">{{ $p->extra('likes_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Replies</dt><dd class="text-xl font-semibold">{{ $p->extra('replies_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Reposts</dt><dd class="text-xl font-semibold">{{ $p->extra('reposts_per_1k') ?? '—' }}</dd></div>
            <div><dt class="text-xs text-ink-500">Bookmarks</dt><dd class="text-xl font-semibold">{{ $p->extra('bookmarks_per_1k') ?? '—' }}</dd></div>
        </dl>
        <p class="border-t border-ink-100 pt-3 text-xs text-ink-500">X doesn’t share who the audience is, so there’s no age, gender or country breakdown for X accounts.</p>
    </x-section-card>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
    @include('creators.partials.profile.content-table', [
        'columns' => [
            'Likes' => 'likes',
            'Replies' => 'replies',
            'Reposts' => 'reposts',
            'Profile visits' => 'profile_clicks',
            'Link clicks' => 'url_clicks',
        ],
        'sparkline' => false,
    ])
    @include('creators.partials.profile.sidebar', ['totals' => array_filter([
        'Posts' => $i->account->public_data['post_count'] ?? null,
        'Following' => $i->account->public_data['following_count'] ?? null,
    ], fn ($v) => $v !== null)])
</div>
