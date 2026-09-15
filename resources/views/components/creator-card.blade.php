@props(['creator'])
@php
    $account = $creator->primaryAccount();
    $state = $creator->profileState();
    $performance = $account?->hasVerifiedMetrics() ? $account->performanceFor($account->platform->primaryContentType()) : null;
    $type = $account?->platform->primaryContentType();
@endphp
<a href="{{ route('creators.show', $creator) }}" {{ $attributes->class(['card flex flex-col p-4 hover:border-ink-400 transition-colors']) }}>
    <div class="flex items-start gap-3">
        <x-avatar :creator="$creator" size="md" />
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-ink-950 truncate leading-5">{{ $creator->name }}</p>
            @if($account)
                <p class="text-sm text-ink-500 truncate">{{ $account->handleWithAt() }}</p>
            @endif
        </div>
        @if($account)
            <span title="{{ $account->platform->label() }}"><x-platform-icon :platform="$account->platform" :colored="true" /></span>
        @endif
    </div>

    <div class="mt-3 flex items-center gap-2 text-xs text-ink-500">
        @if($creator->category)
            <span>{{ $creator->category->name }}</span>
            <span aria-hidden="true">·</span>
        @endif
        <span>{{ $creator->socialAccounts->map(fn ($a) => $a->platform->label())->implode(', ') }}</span>
    </div>

    <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 tnum">
        <div>
            <dt class="text-[11px] text-ink-500">{{ $account ? ucfirst($account->platform->audienceNoun()) : 'Audience' }}</dt>
            <dd class="text-sm font-semibold text-ink-950">{{ \App\Support\Format::compact($account?->follower_count) }}</dd>
        </div>
        @if($performance)
            <div>
                <dt class="text-[11px] text-ink-500">Median views</dt>
                <dd class="text-sm font-semibold text-ink-950">{{ \App\Support\Format::compact($performance->median_views) }}</dd>
            </div>
            <div>
                @if($account->platform === \App\Enums\Platform::YouTube)
                    <dt class="text-[11px] text-ink-500">Watched</dt>
                    <dd class="text-sm font-semibold text-ink-950">{{ \App\Support\Format::percent($performance->average_view_percentage, 0) }}</dd>
                @elseif($account->platform === \App\Enums\Platform::Instagram)
                    <dt class="text-[11px] text-ink-500">Median reach</dt>
                    <dd class="text-sm font-semibold text-ink-950">{{ \App\Support\Format::compact($performance->median_reach) }}</dd>
                @else
                    <dt class="text-[11px] text-ink-500">Engagement</dt>
                    <dd class="text-sm font-semibold text-ink-950">{{ \App\Support\Format::percent($performance->engagement_rate) }}</dd>
                @endif
            </div>
        @else
            <div class="col-span-2">
                <dt class="text-[11px] text-ink-500">Performance</dt>
                <dd class="text-sm text-ink-400">Public info only</dd>
            </div>
        @endif
    </dl>

    <div class="mt-3 flex items-center justify-between">
        <x-state-badge :state="$state" />
        <span class="text-xs font-medium text-ink-700">View profile →</span>
    </div>
</a>
