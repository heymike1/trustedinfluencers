<x-slot:heading>{{ $creator->name }}</x-slot:heading>
<x-slot:subheading>/{{ $creator->slug }} · {{ $state->value }} · created {{ $creator->created_at->format('j M Y') }}</x-slot:subheading>
<x-slot:actions>
    <a href="{{ route('creators.show', $creator) }}" class="btn-secondary btn-sm">View public profile</a>
    <a href="{{ route('admin.creators') }}" class="btn-secondary btn-sm">All creators</a>
</x-slot:actions>
<div>
    <x-notice :notice="$notice" />

    @if($creator->mergedInto)
        <p class="mb-4 rounded-xl border border-warn-100 bg-amber-50 px-4 py-3 text-sm text-warn-700">Merged into <a href="{{ route('admin.creators.show', $creator->mergedInto) }}" class="font-semibold underline">{{ $creator->mergedInto->name }}</a>. This profile redirects there.</p>
    @endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-5">
            {{-- Editable fields --}}
            <form wire:submit="save" class="card p-5 space-y-4">
                <h2 class="text-[15px] font-semibold text-ink-950">Profile</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="f-name">Name</label>
                        <input id="f-name" type="text" wire:model="form.name" class="input">
                        <x-field-error for="form.name" />
                    </div>
                    <div>
                        <label class="label" for="f-slug">Slug</label>
                        <input id="f-slug" type="text" wire:model="form.slug" class="input">
                        <x-field-error for="form.slug" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="f-cat">Category</label>
                        <select id="f-cat" wire:model="form.creator_category_id" class="input">
                            <option value="">None</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                        <x-field-error for="form.creator_category_id" />
                    </div>
                    <div>
                        <label class="label" for="f-status">Status</label>
                        <select id="f-status" wire:model="form.status" class="input">
                            @foreach($statuses as $s)<option value="{{ $s->value }}">{{ ucfirst($s->value) }}</option>@endforeach
                        </select>
                        <p class="mt-1 text-xs text-ink-400">Hidden takes it out of the directory for everyone; the creator's own switch is separate.</p>
                        <x-field-error for="form.status" />
                    </div>
                </div>
                <div>
                    <label class="label" for="f-bio">Bio</label>
                    <textarea id="f-bio" rows="3" wire:model="form.bio" class="input" maxlength="500"></textarea>
                    <x-field-error for="form.bio" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="f-loc">Location</label>
                        <input id="f-loc" type="text" wire:model="form.location" class="input">
                        <x-field-error for="form.location" />
                    </div>
                    <div>
                        <label class="label" for="f-web">Website</label>
                        <input id="f-web" type="url" wire:model="form.website" class="input">
                        <x-field-error for="form.website" />
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="f-avatar">Avatar URL</label>
                        <input id="f-avatar" type="url" wire:model="form.avatar_url" class="input">
                        <x-field-error for="form.avatar_url" />
                    </div>
                    <div>
                        <label class="label" for="f-mail">Contact email</label>
                        <input id="f-mail" type="email" wire:model="form.contact_email" class="input">
                        <x-field-error for="form.contact_email" />
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-switch-row model="form.contact_enabled" title="Contact requests" description="Visitors can message this creator." />
                    <x-switch-row model="form.is_listed" title="Listed in the directory" description="The creator's own visibility switch." />
                </div>
                <div class="flex justify-end"><button type="submit" class="btn-primary" wire:loading.attr="disabled">Save changes</button></div>
            </form>

            {{-- Accounts --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 class="text-[15px] font-semibold text-ink-950">Social accounts</h2>
                    <button type="button" wire:click="recalculate" class="btn-secondary btn-sm">Rebuild summary</button>
                </div>
                <table class="data-table">
                    <thead><tr><th class="pl-5">Account</th><th>Status</th><th class="text-right">Audience</th><th class="text-right">Content</th><th>Last sync</th><th class="pr-5"></th></tr></thead>
                    <tbody>
                        @forelse($creator->socialAccounts as $account)
                            <tr wire:key="acc-{{ $account->id }}">
                                <td class="pl-5">
                                    <p class="flex items-center gap-1.5 font-medium text-ink-950"><x-platform-icon :platform="$account->platform" class="size-3.5" :colored="true" /> {{ $account->handleWithAt() }}</p>
                                    <p class="text-xs text-ink-500">{{ $account->platform->label() }} · ID {{ $account->provider_account_id ?? '—' }}</p>
                                </td>
                                <td><x-badge :variant="$account->isConnected() ? 'verified' : ($account->connection_status->value === 'unconnected' ? 'neutral' : 'warn')">{{ $account->connection_status->label() }}</x-badge></td>
                                <td class="text-right tnum">{{ \App\Support\Format::compact($account->follower_count) }}</td>
                                <td class="text-right tnum"><a href="{{ route('admin.content', ['account' => $account->id]) }}" class="text-brand-700 hover:underline">{{ $account->contents()->count() }}</a></td>
                                <td class="text-xs text-ink-500 whitespace-nowrap">{{ $account->last_synced_at?->diffForHumans() ?? '—' }}</td>
                                <td class="pr-5 text-right whitespace-nowrap">
                                    @if($account->isConnected())
                                        <button type="button" wire:click="sync({{ $account->id }})" class="btn-secondary btn-sm">Sync</button>
                                        <button type="button" wire:click="disconnect({{ $account->id }})" wire:confirm="Disconnect {{ $account->platform->label() }}? Imported data is deleted." class="btn-secondary btn-sm">Disconnect</button>
                                    @endif
                                    <button type="button" wire:click="removeAccount({{ $account->id }})" wire:confirm="Remove {{ $account->handleWithAt() }} from this profile?" class="btn-danger btn-sm">Remove</button>
                                </td>
                            </tr>
                            @if($account->last_sync_error)
                                <tr wire:key="err-{{ $account->id }}"><td colspan="6" class="pl-5 pr-5 pt-0 text-xs text-warn-700">{{ $account->last_sync_error }}</td></tr>
                            @endif
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-sm text-ink-500">No accounts on this profile.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Claims --}}
            <div class="card overflow-hidden">
                <h2 class="px-5 py-4 text-[15px] font-semibold text-ink-950">Claims</h2>
                <table class="data-table">
                    <thead><tr><th class="pl-5">User</th><th>Platform</th><th>Status</th><th>Detail</th><th class="pr-5">When</th></tr></thead>
                    <tbody>
                        @forelse($creator->claims as $claim)
                            <tr wire:key="claim-{{ $claim->id }}">
                                <td class="pl-5 text-xs">{{ $claim->user?->email ?? '—' }}</td>
                                <td class="text-xs">{{ $claim->platform->label() }}</td>
                                <td><x-badge :variant="$claim->status->value === 'verified' ? 'verified' : ($claim->status->value === 'failed' ? 'danger' : 'neutral')">{{ ucfirst($claim->status->value) }}</x-badge></td>
                                <td class="text-xs text-ink-500">{{ $claim->failure_reason ?? ($claim->returned_handle ? '@'.$claim->returned_handle : '—') }}</td>
                                <td class="pr-5 text-xs text-ink-500 whitespace-nowrap">{{ $claim->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-sm text-ink-500">Nobody has tried to claim this profile.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Contact requests --}}
            <div class="card overflow-hidden">
                <h2 class="px-5 py-4 text-[15px] font-semibold text-ink-950">Contact requests</h2>
                <table class="data-table">
                    <thead><tr><th class="pl-5">From</th><th>Subject</th><th>Read</th><th class="pr-5">When</th></tr></thead>
                    <tbody>
                        @forelse($creator->contactRequests as $request)
                            <tr wire:key="req-{{ $request->id }}">
                                <td class="pl-5 text-xs">{{ $request->name }}<br><span class="text-ink-500">{{ $request->email }}</span></td>
                                <td class="text-xs">{{ $request->subject }}</td>
                                <td class="text-xs text-ink-500">{{ $request->read_at ? 'Yes' : 'No' }}</td>
                                <td class="pr-5 text-xs text-ink-500 whitespace-nowrap">{{ $request->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-sm text-ink-500">No messages yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5">
                <h2 class="text-[15px] font-semibold text-ink-950">Summary columns</h2>
                <p class="text-xs text-ink-500">Denormalised for the directory. Rebuilt on every sync.</p>
                <dl class="mt-3 space-y-2 text-[13px] tnum">
                    @foreach([
                        'Followers' => \App\Support\Format::compact($creator->follower_count),
                        'Median views' => \App\Support\Format::compact($creator->median_views),
                        'Average views' => \App\Support\Format::compact($creator->average_views),
                        'Engagement' => \App\Support\Format::percent($creator->engagement_rate),
                        'Watched' => \App\Support\Format::percent($creator->average_view_percentage, 0),
                        'Posts / month' => $creator->posts_per_month ? round($creator->posts_per_month, 1) : '—',
                        'Primary platform' => $creator->primary_platform?->label() ?? '—',
                        'Metrics synced' => $creator->metrics_synced_at?->diffForHumans() ?? '—',
                    ] as $label => $value)
                        <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ $label }}</dt><dd class="font-medium text-ink-950">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </div>

            <div class="card p-5 space-y-3">
                <h2 class="text-[15px] font-semibold text-ink-950">Owner</h2>
                @if($creator->user)
                    <p class="text-[13px] text-ink-700">{{ $creator->user->name }}<br><span class="text-ink-500">{{ $creator->user->email }}</span></p>
                    <button type="button" wire:click="releaseClaim" wire:confirm="Release this claim? Accounts are disconnected and imported data is deleted." class="btn-danger btn-sm">Release claim</button>
                @else
                    <p class="text-[13px] text-ink-500">Unclaimed.</p>
                @endif
                <div class="border-t border-ink-100 pt-3">
                    <label class="label" for="transfer">Transfer to user</label>
                    <div class="flex gap-2">
                        <input id="transfer" type="text" wire:model="transferTo" class="input" placeholder="email or ID">
                        <button type="button" wire:click="transfer" class="btn-secondary btn-sm shrink-0">Transfer</button>
                    </div>
                    <x-field-error for="transferTo" />
                </div>
            </div>

            <div class="card p-5 space-y-3">
                <h2 class="text-[15px] font-semibold text-ink-950">Merge</h2>
                <p class="text-[13px] text-ink-500">Moves the accounts onto another profile and leaves a redirect behind.</p>
                <div class="flex gap-2">
                    <input type="text" wire:model="mergeInto" class="input" placeholder="target slug or ID">
                    <button type="button" wire:click="merge" wire:confirm="Merge {{ $creator->name }} into that profile?" class="btn-secondary btn-sm shrink-0">Merge</button>
                </div>
                <x-field-error for="mergeInto" />
            </div>

            <div class="card border-danger-100 p-5 space-y-2">
                <h2 class="text-[15px] font-semibold text-ink-950">Delete</h2>
                <p class="text-[13px] text-ink-500">Removes the profile, its accounts, imported content, metrics and messages. No undo.</p>
                <button type="button" wire:click="destroy" wire:confirm.prompt="Delete {{ $creator->name }} and everything under it?\n\nType DELETE to confirm.|DELETE" class="btn-danger btn-sm">Delete profile</button>
            </div>
        </div>
    </div>
</div>
