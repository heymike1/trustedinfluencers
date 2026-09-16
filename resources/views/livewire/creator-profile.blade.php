@php
    use App\Enums\Platform;
    use App\Support\Format;
@endphp
<div @if($importing) wire:poll.8s @endif>
    @if($accounts->count() > 1)
        <div class="flex flex-wrap gap-1.5 mb-6">
            @foreach($accounts as $a)
                <button type="button" wire:click="$set('platform', '{{ $a->platform->value }}')" class="chip !py-2 {{ $a->is($account) ? 'chip-on' : '' }}">
                    <x-platform-icon :platform="$a->platform" class="size-3.5" :colored="! $a->is($account)" /> {{ $a->platform->label() }}
                    @if($a->hasVerifiedMetrics())<span class="size-1.5 rounded-full {{ $a->is($account) ? 'bg-white/80' : 'bg-brand-600' }}"></span>@endif
                </button>
            @endforeach
        </div>
    @endif

    @if(! $account)
        <div class="card p-8 text-center text-sm text-ink-500">No social accounts on this profile yet.</div>
    @elseif($verified && $insights)
        {{-- Stat strip --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
            <h2 class="display text-xl tracking-[-0.02em]">What a sponsor gets, per {{ $activeType->singular() }}</h2>
            <div class="flex flex-wrap gap-1">
                @foreach($windows as $w)
                    <button type="button" wire:click="$set('window', '{{ $w->value }}')" class="chip {{ $activeWindow === $w ? 'chip-on' : '' }}">{{ $w->label() }}</button>
                @endforeach
                @if(count($types) > 1)
                    <span class="mx-1 w-px bg-ink-200"></span>
                    @foreach($types as $t)
                        <button type="button" wire:click="$set('type', '{{ $t->value }}')" class="chip {{ $activeType === $t ? 'chip-on' : '' }}">{{ ucfirst($t->label()) }}</button>
                    @endforeach
                @endif
            </div>
        </div>

        @include('creators.partials.profile.'.$account->platform->value, ['i' => $insights, 'p' => $insights->performance, 'audience' => $insights->audience, 'contents' => $insights->contents, 'type' => $activeType])

        <p class="mt-3 text-xs text-ink-400">Median views is what a typical {{ $activeType->singular() }} gets: half did better, half did worse, and one viral hit doesn’t drag it up. Average views counts every {{ $activeType->singular() }}, viral hits included, so it’s usually higher. Engagement is likes, comments, shares and saves per view. {{ ucfirst($activeType->label()) }} are never mixed with other content types.</p>
    @elseif($account->isImporting())
        <div class="card p-6 flex items-center gap-3 text-sm text-ink-700">
            <span class="size-2 rounded-full bg-brand-600 animate-pulse"></span>
            Pulling in content and stats from {{ $account->platform->label() }}. This page updates on its own.
        </div>
    @elseif($verified)
        <div class="card p-6 text-sm text-ink-500">No {{ $activeWindow->label() }} data for this {{ $account->platform->label() }} account yet.</div>
    @else
        <div class="card p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-ink-900"><x-platform-icon :platform="$account->platform" class="size-5" /></span>
                    <div>
                        <p class="text-sm font-semibold text-ink-950">{{ $account->platform->label() }} <span class="font-normal text-ink-500">{{ $account->handleWithAt() }}</span></p>
                        <p class="text-xs text-ink-500 tnum"><span class="font-medium text-ink-900">{{ Format::compact($account->follower_count) }}</span> {{ $account->platform->audienceNoun() }}</p>
                    </div>
                </div>
                @if($account->connection_status === \App\Enums\ConnectionStatus::NeedsReconnection)
                    <x-badge variant="warn">Needs reconnection</x-badge>
                @elseif($account->connection_status === \App\Enums\ConnectionStatus::SyncFailed)
                    <x-badge variant="warn">Sync failed</x-badge>
                @else
                    <x-badge>Public info only</x-badge>
                @endif
            </div>
            <p class="mt-4 text-sm text-ink-500">
                @if($state === \App\Enums\ProfileState::NeedsReconnection)
                    The connection to this account expired. The numbers come back once the creator reconnects.
                @elseif($state->isClaimed())
                    The creator owns this profile but hasn’t connected {{ $account->platform->label() }} yet, so there are no verified numbers for it.
                @else
                    Verified numbers (median views, how much gets watched, who the audience is) show up once the creator connects this {{ $account->platform->label() }} account.
                @endif
            </p>
        </div>
    @endif

    {{-- Contact --}}
    <section id="contact" class="mt-10 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] scroll-mt-6">
        <div>
            <h2 class="display text-xl tracking-[-0.02em] mb-3">Contact {{ $creator->name }}</h2>
            <livewire:contact-creator-form :creator="$creator" />
        </div>
        <aside class="text-sm text-ink-500 space-y-3 lg:pt-9">
            <p><span class="font-medium text-ink-900">Public info</span> is what anyone can see on the platform, or what the person who added the profile typed in.</p>
            <p><span class="font-medium text-ink-900">Verified metrics</span> only show up after the creator signs in with the account itself. They come straight from the platform and can’t be edited by hand.</p>
            <p>This isn’t the platform’s own verification badge (blue tick, X Premium and so on).</p>
        </aside>
    </section>
</div>
