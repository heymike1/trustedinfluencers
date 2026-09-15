@php
    $platforms = $creator->socialAccounts->map(fn ($a) => $a->platform->label())->implode(', ');
    $title = trim($creator->name.' '.$platforms).' Stats & Verified Creator Metrics';
    $primary = $creator->primaryAccount();
    $description = $creator->name.' on '.$platforms.($primary?->follower_count ? ', '.\App\Support\Format::compact($primary->follower_count).' '.$primary->platform->audienceNoun() : '').'. '.($creator->has_verified_metrics ? 'Numbers verified through the creator’s own account.' : 'Public info only. If this is you, claim the profile to show your real numbers.');
    $state = $creator->profileState();
    $isOwner = $creator->isOwnedBy(auth()->user());
    $importing = $creator->socialAccounts->contains(fn ($a) => $a->isImporting());
    $connected = $creator->socialAccounts->filter->hasVerifiedMetrics();
@endphp
<x-layouts.app :title="$title" :description="$description" :canonical="route('creators.show', $creator)" :json-ld="\App\Support\Seo::creator($creator)" :noindex="! $creator->is_listed">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-6 pb-8">
                @unless($creator->is_listed)
                    <p class="mb-4 rounded-md border border-warn-100 bg-amber-50 px-3.5 py-2.5 text-sm text-warn-700">Only you can see this page. Your profile is hidden from the directory; turn the listing back on under <a href="{{ route('account') }}" class="underline underline-offset-2">Profile</a>.</p>
                @endunless
                <p class="text-xs text-ink-500 flex gap-1.5">
                    <a href="{{ route('creators.index') }}" class="hover:text-ink-950">Creators</a>
                    @if($creator->category)<span>/</span><a href="{{ route('creators.index', ['category' => $creator->category->slug]) }}" class="hover:text-ink-950">{{ $creator->category->name }}</a>@endif
                </p>
                <div class="mt-4 flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4 min-w-0">
                        <x-avatar :creator="$creator" size="xl" />
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl font-semibold tracking-tight text-ink-950">{{ $creator->name }}</h1>
                                @if($state === \App\Enums\ProfileState::VerifiedMetrics)
                                    <x-badge variant="verified" class="gap-1.5">
                                        <svg class="size-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg>
                                        Verified through
                                        @foreach($connected as $a)
                                            <span class="inline-flex items-center gap-1"><x-platform-icon :platform="$a->platform" class="size-3.5" :colored="true" />{{ $a->platform->label() }}</span>@if(! $loop->last)<span class="text-verified-600/60">{{ $loop->remaining === 1 ? 'and' : '' }}</span>@endif
                                        @endforeach
                                    </x-badge>
                                @else
                                    <x-state-badge :state="$state" />
                                @endif
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-600">
                                @foreach($creator->socialAccounts as $account)
                                    <a href="{{ $account->profile_url }}" target="_blank" rel="noopener nofollow" class="inline-flex items-center gap-1.5 hover:text-ink-950"><x-platform-icon :platform="$account->platform" class="size-4" :colored="true" /> {{ $account->handleWithAt() }}</a>
                                @endforeach
                                @if($creator->category)<span class="text-ink-300">·</span><span>{{ $creator->category->name }}</span>@endif
                                @if($creator->location)<span class="text-ink-300">·</span><span>{{ $creator->location }}</span>@endif
                                @if($creator->website)<span class="text-ink-300">·</span><a href="{{ $creator->website }}" target="_blank" rel="noopener nofollow" class="hover:text-ink-950">{{ \Illuminate\Support\Str::of($creator->website)->replaceMatches('~^https?://(www\.)?~', '')->rtrim('/') }}</a>@endif
                            </div>
                            @if($creator->bio)<p class="mt-3 max-w-xl text-sm text-ink-700">{{ $creator->bio }}</p>@endif
                            @if($ranks)
                                <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-500 tnum">
                                    @foreach($ranks as $rank)
                                        <span><span class="font-semibold text-ink-900">#{{ $rank['rank'] }}</span> {{ $rank['hint'] }}</span>
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="card w-full md:w-72 shrink-0 p-4 text-sm">
                        @if($isOwner)
                            <p class="font-medium text-ink-950">This is your profile</p>
                            <p class="mt-1 text-ink-500">Edit details or manage connected accounts from your dashboard.</p>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('account') }}" class="btn-primary btn-sm">Edit profile</a>
                                <a href="{{ route('account.connections') }}" class="btn-secondary btn-sm">Connections</a>
                            </div>
                        @elseif(! $state->isClaimed())
                            <p class="font-medium text-ink-950">Is this you?</p>
                            <p class="mt-1 text-ink-500">Sign in with the social account this profile belongs to and it’s yours. We’ll pull your numbers straight from the platform.</p>
                            <a href="{{ route('creators.claim', $creator) }}" class="btn-primary btn-sm mt-3 w-full">Claim this profile</a>
                        @elseif($state === \App\Enums\ProfileState::VerifiedMetrics || $state === \App\Enums\ProfileState::MetricsOutdated)
                            <p class="font-medium text-ink-950">Verified metrics</p>
                            <p class="mt-1 text-ink-500">{{ \Illuminate\Support\Str::before($creator->name, ' ') }} connected {{ $connected->map(fn ($a) => $a->platform->label())->join(', ', ' and ') }}. Every number below comes straight from there.</p>
                            @if($creator->metrics_synced_at)<p class="mt-2 text-xs text-ink-400">Last updated {{ $creator->metrics_synced_at->diffForHumans() }}</p>@endif
                            @if($creator->contact_enabled)<a href="#contact" class="btn-primary btn-sm mt-3 w-full">Contact {{ \Illuminate\Support\Str::before($creator->name, ' ') }}</a>@endif
                        @elseif($importing)
                            <p class="font-medium text-ink-950 flex items-center gap-2"><span class="size-2 rounded-full bg-brand-600 animate-pulse"></span>Importing numbers</p>
                            <p class="mt-1 text-ink-500">Account confirmed. We’re pulling in the content and stats now.</p>
                        @elseif($state === \App\Enums\ProfileState::NeedsReconnection)
                            <div class="flex items-center justify-between gap-2"><p class="font-medium text-ink-950">Claimed</p><x-state-badge :state="$state" /></div>
                            <p class="mt-1 text-ink-500">The connection to the creator’s account expired. The numbers come back once they reconnect.</p>
                            @if($creator->contact_enabled)<a href="#contact" class="btn-secondary btn-sm mt-3 w-full">Contact {{ \Illuminate\Support\Str::before($creator->name, ' ') }}</a>@endif
                        @else
                            <div class="flex items-center justify-between gap-2"><p class="font-medium text-ink-950">Claimed</p><x-state-badge :state="$state" /></div>
                            <p class="mt-1 text-ink-500">The creator owns this profile but has no account connected right now, so there are no verified numbers.</p>
                            @if($creator->contact_enabled)<a href="#contact" class="btn-secondary btn-sm mt-3 w-full">Contact {{ \Illuminate\Support\Str::before($creator->name, ' ') }}</a>@endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:hero>

    <livewire:creator-profile :creator="$creator" />
</x-layouts.app>
