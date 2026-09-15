@php
    use App\Enums\Platform;
    use App\Support\Format;
    $isYT = $platform === Platform::YouTube;
    $isIG = $platform === Platform::Instagram;
    $isX = $platform === Platform::X;
@endphp
<div class="card overflow-x-auto" wire:loading.class="opacity-60">
    <table class="data-table tnum">
        <thead>
            <tr>
                <th class="w-8 pl-4">#</th>
                <th>Creator</th>
                <th class="text-right">{{ $isYT ? 'Subs' : 'Audience' }}</th>
                <th class="text-right {{ $sort === 'median_views' ? 'text-ink-950' : '' }}">Median views</th>
                @if($isYT)
                    <th class="text-right">Watched</th>
                    <th class="text-right">Avg. watch</th>
                    <th>Watch curve</th>
                @elseif($isIG)
                    <th class="text-right">Median reach</th>
                    <th class="text-right">New viewers</th>
                    <th class="text-right">Avg. watch</th>
                    <th class="text-right">Saves / 1K</th>
                @elseif($isX)
                    <th class="text-right">Profile visits</th>
                    <th class="text-right">Link clicks</th>
                    <th class="text-right">Replies / 1K</th>
                @else
                    <th class="text-right">Average views</th>
                @endif
                <th class="text-right">Engagement</th>
                <th class="text-right">Views vs. audience</th>
                @if(! $isX)<th>Top country · age</th>@endif
                <th class="pr-4">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creators as $i => $creator)
                @php
                    $account = $platform ? $creator->socialAccounts->firstWhere('platform', $platform) : $creator->primaryAccount();
                    $perf = $account?->hasVerifiedMetrics() ? $account->performanceFor($account->platform->primaryContentType()) : null;
                    $audience = $account?->audience;
                    $audienceLine = \App\View\AudienceSummary::line($audience);
                    $ratio = $perf ? $account->viewsVsAudience($perf) : null;
                    $state = $creator->profileState();
                @endphp
                <tr wire:key="row-{{ $creator->id }}" class="{{ $perf ? '' : 'text-ink-400' }}">
                    <td class="pl-4 text-ink-500">{{ $offset + $i + 1 }}</td>
                    <td>
                        <a href="{{ route('creators.show', $creator) }}" class="flex items-center gap-2.5 min-w-0">
                            <x-avatar :creator="$creator" size="sm" />
                            <span class="min-w-0">
                                <span class="block font-semibold text-ink-950 truncate leading-[18px]">{{ $creator->name }}</span>
                                <span class="flex items-center gap-1 text-xs text-ink-500 truncate">@if($account)<x-platform-icon :platform="$account->platform" class="size-3 shrink-0" :colored="true" /> {{ $account->handleWithAt() }}@endif @if($creator->category)<span class="text-ink-300">·</span> {{ $creator->category->name }}@endif</span>
                            </span>
                        </a>
                    </td>
                    <td class="text-right text-ink-700">{{ Format::compact($account?->follower_count) }}</td>
                    <td class="text-right font-semibold {{ $perf ? 'text-ink-950' : '' }}">{{ Format::compact($perf?->median_views) }}</td>
                    @if($isYT)
                        <td class="text-right">{{ Format::percent($perf?->average_view_percentage, 0) }}</td>
                        <td class="text-right">{{ Format::duration($perf?->average_watch_time) }}</td>
                        <td>@if($perf?->extra('retention_curve'))<x-sparkline :points="$perf->extra('retention_curve')" />@else —@endif</td>
                    @elseif($isIG)
                        <td class="text-right">{{ Format::compact($perf?->median_reach) }}</td>
                        <td class="text-right">{{ $audience?->follower_type ? Format::percent($audience->follower_type['non_follower'] ?? null, 0) : '—' }}</td>
                        <td class="text-right">{{ Format::duration($perf?->average_watch_time) }}</td>
                        <td class="text-right">{{ $perf?->extra('saves_per_1k') !== null ? number_format($perf->extra('saves_per_1k'), 1) : '—' }}</td>
                    @elseif($isX)
                        <td class="text-right">{{ Format::compact($perf?->extra('average_profile_clicks')) }}</td>
                        <td class="text-right">{{ Format::compact($perf?->extra('average_url_clicks')) }}</td>
                        <td class="text-right">{{ $perf?->extra('replies_per_1k') !== null ? number_format($perf->extra('replies_per_1k'), 1) : '—' }}</td>
                    @else
                        <td class="text-right">{{ Format::compact($perf?->average_views) }}</td>
                    @endif
                    <td class="text-right">{{ Format::percent($perf?->engagement_rate) }}</td>
                    <td class="text-right {{ $ratio !== null && $ratio < 10 ? 'text-warn-700' : '' }}">{{ $ratio === null ? '—' : Format::percent($ratio, 0) }}</td>
                    @if(! $isX)
                        <td class="text-xs whitespace-nowrap">{{ $audienceLine ?? '—' }}</td>
                    @endif
                    <td class="pr-4"><x-state-badge :state="$state" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-2.5 border-t border-ink-200 bg-ink-50 text-xs text-ink-500">
        Median views is what a typical piece of content gets, so one viral hit doesn’t inflate it. Unclaimed profiles show their audience size only.
        @if($isIG) “New viewers” is the share of people reached in the last 30 days who don’t follow yet. @endif
        @if($isX) X doesn’t share audience details, so there’s no country or age column. @endif
    </div>
</div>
