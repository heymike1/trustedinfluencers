<div @if($importing) wire:poll.6s @endif>
    @if(! $creator)
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />
        <x-notice :notice="$notice" />

        <div class="grid gap-8 lg:grid-cols-[1fr_300px]">
            <div class="space-y-4">
                @foreach($accounts as $account)
                    @php($status = $account->connection_status)
                    <div class="card p-4" wire:key="account-{{ $account->id }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="text-ink-900"><x-platform-icon :platform="$account->platform" class="size-5" /></span>
                                <div>
                                    <p class="text-sm font-semibold text-ink-950">{{ $account->platform->label() }} <span class="font-normal text-ink-500">{{ $account->handleWithAt() }}</span></p>
                                    <p class="text-xs text-ink-500 tnum">{{ \App\Support\Format::compact($account->follower_count) }} {{ $account->platform->audienceNoun() }}
                                        @if($account->last_synced_at) · last synced {{ $account->last_synced_at->diffForHumans() }} @endif
                                    </p>
                                </div>
                            </div>
                            <div>
                                @switch($status)
                                    @case(\App\Enums\ConnectionStatus::Connected)
                                        <x-badge variant="verified">Verified · Connected</x-badge>
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
                        </div>

                        @if($account->last_sync_error && in_array($status, [\App\Enums\ConnectionStatus::SyncFailed, \App\Enums\ConnectionStatus::NeedsReconnection]))
                            <p class="mt-3 rounded-md border border-warn-100 bg-amber-50 px-3 py-2 text-xs text-warn-700">{{ $account->last_sync_error }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @if($account->isConnected())
                                <button type="button" wire:click="syncNow({{ $account->id }})" class="btn-secondary btn-sm" @if($account->isImporting()) disabled @endif>Sync now</button>
                                <form method="POST" action="{{ route('account.connections.connect', $account) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary btn-sm">Reconnect</button>
                                </form>
                                <button type="button" wire:click="disconnect({{ $account->id }})" wire:confirm="Disconnect {{ $account->platform->label() }}? We’ll delete the verified numbers and stats we pulled in for this account. Your public profile stays." class="btn-danger btn-sm">Disconnect</button>
                            @else
                                <form method="POST" action="{{ route('account.connections.connect', $account) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm">{{ $status === \App\Enums\ConnectionStatus::NeedsReconnection ? 'Reconnect' : 'Connect' }} {{ $account->platform->label() }}</button>
                                </form>
                                <p class="text-xs text-ink-500">Sign in as {{ $account->handleWithAt() }} to confirm it’s yours and pull in your numbers.</p>
                            @endif
                            <button type="button" wire:click="remove({{ $account->id }})" wire:confirm="Remove {{ $account->platform->label() }} from your profile? {{ $account->isConnected() ? 'We’ll delete the verified numbers and stats we pulled in, and ' : 'We’ll take ' }}{{ $account->handleWithAt() }} off your public profile." class="btn-secondary btn-sm text-danger-700 sm:ml-auto">Remove</button>
                        </div>
                    </div>
                @endforeach

                @if($platforms->isNotEmpty())
                    <form wire:submit="addAccount" class="card p-4">
                        <p class="text-sm font-semibold text-ink-950">Add another platform</p>
                        <p class="mt-1 text-xs text-ink-500">This adds it to your profile as public info. Connect it afterwards to get verified numbers.</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-[160px_1fr_auto]">
                            <div>
                                <select wire:model="newPlatform" class="input">
                                    <option value="">Platform</option>
                                    @foreach($platforms as $p)
                                        <option value="{{ $p->value }}">{{ $p->label() }}</option>
                                    @endforeach
                                </select>
                                <x-field-error for="newPlatform" />
                            </div>
                            <div>
                                <input type="text" wire:model="newHandle" class="input" placeholder="@handle or profile URL">
                                <x-field-error for="newHandle" />
                            </div>
                            <button type="submit" class="btn-secondary">Add</button>
                        </div>
                    </form>
                @endif
            </div>

            <aside class="text-sm text-ink-500 space-y-3">
                <p class="font-medium text-ink-900">How syncing works</p>
                <p>We refresh connected accounts every {{ config('social.sync.refresh_every_hours') }} hours on our own. You can run a sync yourself once every {{ $cooldown }} minutes.</p>
                <p class="font-medium text-ink-900 pt-2">Disconnecting</p>
                <p>Disconnecting throws away the login straight away and deletes everything we pulled in for that account, history included. Your public profile (name, handle, last known follower count) stays listed.</p>
                <p class="font-medium text-ink-900 pt-2">Removing</p>
                <p>Removing does the same and also takes the platform off your public profile. To take your whole profile out of the directory, turn off the listing under <a href="{{ route('account') }}" class="underline underline-offset-2 text-ink-900">Profile</a>.</p>
            </aside>
        </div>
    @endif
</div>
