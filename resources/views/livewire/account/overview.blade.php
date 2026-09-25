<div>
    @if(! $creator)
        <x-page-band>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="display text-2xl sm:text-[26px] tracking-[-0.025em]">My profile</h1>
                    <p class="mt-0.5 text-[13.5px] text-ink-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('account.settings') }}" class="btn-secondary border-band-edge">
                    <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="7" r="3"/><path d="M4 17c0-3 2.7-5 6-5s6 2 6 5"/></svg>
                    Manage my account
                </a>
            </div>
        </x-page-band>
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            <div class="space-y-4">
                {{-- What a brand sees --}}
                <section class="card p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 class="text-[15px] font-semibold text-ink-950">What a brand sees</h2>
                        <p class="text-xs text-ink-500">
                            @if($creator->metrics_synced_at)
                                Synced {{ $creator->metrics_synced_at->diffForHumans() }}@if($nextRefresh) · next {{ $nextRefresh->isPast() ? 'any minute' : $nextRefresh->diffForHumans() }}@endif
                            @else
                                Nothing imported yet
                            @endif
                        </p>
                    </div>

                    @if($headline)
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 tnum">
                            @foreach($headline as $stat)
                                <div>
                                    <dt class="text-xs text-ink-500">{{ $stat['label'] }}</dt>
                                    <dd class="display mt-0.5 text-2xl tracking-[-0.02em]">{{ $stat['value'] }}</dd>
                                    <dd class="text-[11.5px] text-ink-500">{{ $stat['hint'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <a href="{{ route('creators.show', $creator) }}" class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700">See your full profile <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
                    @else
                        <p class="mt-3 text-sm text-ink-500">
                            Your numbers show up here once you connect an account. Until then your profile shows public info only.
                            <a href="{{ route('account.connections') }}" class="font-semibold text-brand-700">Connect a platform</a>.
                        </p>
                    @endif
                </section>

                {{-- Accounts --}}
                <section class="card overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4">
                        <h2 class="text-[15px] font-semibold text-ink-950">Connected accounts</h2>
                        <a href="{{ route('account.connections') }}" class="text-xs text-ink-500 hover:text-ink-950">Manage →</a>
                    </div>
                    @foreach($accounts as $account)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-ink-100 px-5 py-3.5" wire:key="acc-{{ $account->id }}">
                            <span class="flex items-center gap-2.5 text-sm">
                                <x-platform-icon :platform="$account->platform" class="size-4" :colored="true" />
                                <span class="font-semibold text-ink-950">{{ $account->handleWithAt() }}</span>
                                @if($account->isConnected())
                                    <x-badge variant="verified">Connected</x-badge>
                                @elseif($account->connection_status === \App\Enums\ConnectionStatus::NeedsReconnection)
                                    <x-badge variant="warn">Needs reconnection</x-badge>
                                @else
                                    <x-badge>Not connected</x-badge>
                                @endif
                            </span>
                            <span class="text-xs text-ink-500 tnum">
                                @if($account->last_synced_at)
                                    {{ \App\Support\Format::compact($account->follower_count) }} {{ $account->platform->audienceNoun() }} · synced {{ $account->last_synced_at->diffForHumans() }}
                                @elseif($account->platform->isEnabled())
                                    <a href="{{ route('account.connections') }}" class="font-semibold text-brand-700">Connect {{ $account->platform->label() }}</a>
                                @else
                                    Public info only
                                @endif
                            </span>
                        </div>
                    @endforeach
                    @foreach($missingPlatforms as $platform)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-dashed border-ink-200 px-5 py-3.5" wire:key="missing-{{ $platform->value }}">
                            <span class="flex items-center gap-2.5 text-sm text-ink-500"><x-platform-icon :platform="$platform" class="size-4" /> {{ $platform->label() }} not added yet</span>
                            <a href="{{ route('account.connections') }}" class="text-xs font-semibold text-brand-700">Add it</a>
                        </div>
                    @endforeach
                </section>

                {{-- Messages --}}
                <section class="card overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4">
                        <h2 class="text-[15px] font-semibold text-ink-950">Latest messages @if($unread)<span class="font-normal text-ink-500">· {{ $unread }} unread</span>@endif</h2>
                        <a href="{{ route('account.requests') }}" class="text-xs text-ink-500 hover:text-ink-950">All messages →</a>
                    </div>
                    @forelse($messages as $message)
                        <a href="{{ route('account.requests', ['r' => $message->id]) }}" class="flex flex-wrap items-center justify-between gap-2 border-t border-ink-100 px-5 py-3.5 hover:bg-ink-50" wire:key="msg-{{ $message->id }}">
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-ink-950">{{ $message->name }}@if($message->company) <span class="font-normal text-ink-500">· {{ $message->company }}</span>@endif</span>
                                <span class="block truncate text-[13px] text-ink-500">{{ $message->subject }}</span>
                            </span>
                            @unless($message->read_at)<x-badge variant="verified">New</x-badge>@endunless
                        </a>
                    @empty
                        <p class="border-t border-ink-100 px-5 py-6 text-sm text-ink-500">
                            No messages yet. Brands can reach you through your public profile
                            @unless($creator->contact_enabled) once you turn contact requests on under <a href="{{ route('account.profile') }}" class="font-semibold text-brand-700">Edit profile</a>@endunless.
                        </p>
                    @endforelse
                </section>
            </div>

            <aside class="space-y-4">
                @php($done = collect($checklist)->where('done', true)->count())
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[15px] font-semibold text-ink-950">Finish your profile</h2>
                        <span class="text-[13px] text-ink-500 tnum">{{ $done }} of {{ count($checklist) }}</span>
                    </div>
                    <div class="mt-3 h-1.5 rounded-full bg-ink-100"><div class="h-1.5 rounded-full bg-brand-600" style="width: {{ count($checklist) ? round($done / count($checklist) * 100) : 0 }}%"></div></div>
                    <ul class="mt-4 space-y-2.5 text-[13.5px]">
                        @foreach($checklist as $item)
                            <li class="flex items-center gap-2.5 {{ $item['done'] ? 'text-ink-500' : '' }}">
                                @if($item['done'])
                                    <span class="inline-flex size-[18px] shrink-0 items-center justify-center rounded-full bg-verified-50 text-brand-700"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg></span>
                                    <span class="line-through">{{ $item['label'] }}</span>
                                @else
                                    <span class="inline-block size-[18px] shrink-0 rounded-full border-[1.5px] border-ink-200"></span>
                                    @if($item['href'])<a href="{{ $item['href'] }}" class="font-semibold text-brand-700">{{ $item['label'] }}</a>@else<span>{{ $item['label'] }}</span>@endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card p-5 text-[13.5px] text-ink-500 space-y-2">
                    <p class="font-semibold text-ink-950">Where your numbers come from</p>
                    <p>{{ \App\Enums\Platform::enabledLabels(' and ') }}, refreshed every {{ config('social.sync.refresh_every_hours') }} hours. You can sync sooner or disconnect at any time under <a href="{{ route('account.connections') }}" class="font-semibold text-brand-700">Connected accounts</a>.</p>
                </div>
            </aside>
        </div>
    @endif
</div>
