<div @if($importing) wire:poll.6s @endif>
    @if(! $creator)
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />
        <x-notice :notice="$notice" />

        <div class="grid gap-6 lg:grid-cols-[1fr_340px] lg:items-start">
            <div class="space-y-3.5">
                @foreach($accounts as $account)
                    @php
                        $status = $account->connection_status;
                        $perf = $account->hasVerifiedMetrics() ? $account->performanceFor($account->platform->primaryContentType()) : null;
                        $trouble = in_array($status, [\App\Enums\ConnectionStatus::SyncFailed, \App\Enums\ConnectionStatus::NeedsReconnection]);
                    @endphp
                    <div class="card p-6 space-y-4 {{ $trouble ? 'border-warn-100' : '' }}" wire:key="account-{{ $account->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-center gap-3.5">
                                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-ink-200 bg-white"><x-platform-icon :platform="$account->platform" class="size-[22px]" :colored="true" /></span>
                                <div>
                                    <p class="text-[15px] font-semibold text-ink-950">{{ $account->platform->label() }} <span class="font-normal text-ink-500">{{ $account->handleWithAt() }}</span></p>
                                    <p class="text-[13px] text-ink-500 tnum">
                                        {{ \App\Support\Format::compact($account->follower_count) }} {{ $account->platform->audienceNoun() }}
                                        @if($perf?->median_views) · {{ \App\Support\Format::compact($perf->median_views) }} median views @endif
                                        @if($account->last_synced_at) · synced {{ $account->last_synced_at->diffForHumans() }} @elseif(! $account->isConnected()) · public info only @endif
                                    </p>
                                </div>
                            </div>
                            @switch($status)
                                @case(\App\Enums\ConnectionStatus::Connected)
                                    <x-badge variant="verified" class="gap-1.5"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg> Verified · Connected</x-badge>
                                    @break
                                @case(\App\Enums\ConnectionStatus::Importing)
                                @case(\App\Enums\ConnectionStatus::Connecting)
                                    <x-badge><span class="inline-block size-1.5 rounded-full bg-brand-600 animate-pulse"></span> {{ $status->label() }}</x-badge>
                                    @break
                                @case(\App\Enums\ConnectionStatus::NeedsReconnection)
                                @case(\App\Enums\ConnectionStatus::SyncFailed)
                                    <x-badge variant="warn">{{ $status->label() }}</x-badge>
                                    @break
                                @default
                                    <x-badge>{{ $status->label() }}</x-badge>
                            @endswitch
                        </div>

                        @if($account->last_sync_error && $trouble)
                            <p class="rounded-xl border border-warn-100 bg-amber-50 px-3.5 py-2.5 text-[13px] text-warn-700">{{ $account->last_sync_error }} Your public info stays; verified numbers pause until you reconnect.</p>
                        @endif

                        <div class="flex flex-wrap items-center gap-2">
                            @if($account->isConnected())
                                <button type="button" wire:click="syncNow({{ $account->id }})" class="btn-secondary btn-sm" @if($account->isImporting()) disabled @endif>Sync now</button>
                                <form method="POST" action="{{ route('account.connections.connect', $account) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary btn-sm">Reconnect</button>
                                </form>
                                <button type="button" wire:click="disconnect({{ $account->id }})" wire:confirm="Disconnect {{ $account->platform->label() }}? We’ll delete the verified numbers and stats we pulled in for this account. Your public profile stays." class="btn-danger btn-sm sm:ml-auto">Disconnect</button>
                            @else
                                <form method="POST" action="{{ route('account.connections.connect', $account) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm">{{ $status === \App\Enums\ConnectionStatus::NeedsReconnection ? 'Reconnect' : 'Connect' }} {{ $account->platform->label() }}</button>
                                </form>
                                <p class="text-[13px] text-ink-500">Sign in as {{ $account->handleWithAt() }} to confirm it’s yours and pull in your numbers.</p>
                            @endif
                            <button type="button" wire:click="remove({{ $account->id }})" wire:confirm="Remove {{ $account->platform->label() }} from your profile? {{ $account->isConnected() ? 'We’ll delete the verified numbers and stats we pulled in, and ' : 'We’ll take ' }}{{ $account->handleWithAt() }} off your public profile." class="btn-secondary btn-sm text-danger-700 {{ $account->isConnected() ? '' : 'sm:ml-auto' }}">Remove</button>
                        </div>
                    </div>
                @endforeach

                @if($platforms->isNotEmpty())
                    <form wire:submit="addAccount" class="card border-dashed p-6 space-y-4">
                        <div>
                            <p class="text-[15px] font-semibold text-ink-950">Add another platform</p>
                            <p class="text-[13px] text-ink-500">Pick the platform, paste your handle or profile link. It shows as public info until you connect it.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-[auto_1fr_auto] sm:items-end">
                            <div>
                                <span class="label">Platform</span>
                                <div class="flex gap-1.5">
                                    @foreach($platforms as $p)
                                        <button type="button" wire:click="$set('newPlatform', '{{ $p->value }}')" class="chip !py-2 {{ $newPlatform === $p->value ? 'chip-on' : '' }}"><x-platform-icon :platform="$p" class="size-3.5" :colored="$newPlatform !== $p->value" /> {{ $p->label() }}</button>
                                    @endforeach
                                </div>
                                <x-field-error for="newPlatform" />
                            </div>
                            <div>
                                <label class="label" for="newHandle">Handle or profile URL</label>
                                <input id="newHandle" type="text" wire:model="newHandle" class="input" placeholder="@handle or profile URL">
                                <x-field-error for="newHandle" />
                            </div>
                            <button type="submit" class="btn-secondary">Add</button>
                        </div>
                    </form>
                @endif
            </div>

            <aside class="space-y-4 text-[13.5px]">
                <div class="card p-5 space-y-3">
                    <p class="font-semibold text-ink-950">How syncing works</p>
                    <dl class="space-y-2 tnum">
                        <div class="flex justify-between gap-3"><dt class="text-ink-500">Automatic refresh</dt><dd class="font-semibold text-ink-950">every {{ config('social.sync.refresh_every_hours') }} hours</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-ink-500">Manual sync</dt><dd class="font-semibold text-ink-950">once every {{ $cooldown }} min</dd></div>
                        @if($nextRefresh)
                            <div class="flex justify-between gap-3"><dt class="text-ink-500">Next automatic refresh</dt><dd class="font-semibold text-ink-950">{{ $nextRefresh->isPast() ? 'any minute' : $nextRefresh->diffForHumans() }}</dd></div>
                        @endif
                    </dl>
                </div>
                <div class="card p-5 space-y-2.5 text-ink-500">
                    <p class="font-semibold text-ink-950">Disconnect vs. remove</p>
                    <p><span class="font-semibold text-ink-900">Disconnect</span> throws away the login straight away and deletes everything we pulled in, history included. Your public profile (name, handle, last known follower count) stays listed.</p>
                    <p><span class="font-semibold text-ink-900">Remove</span> does the same and also takes the platform off your public profile.</p>
                    <p>We never see your password and you can revoke access on the platform itself at any time.</p>
                </div>
            </aside>
        </div>
    @endif
</div>
