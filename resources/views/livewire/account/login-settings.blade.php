<div>
    @if($creator)
        <x-account-nav :creator="$creator" />
    @else
        <x-page-band>
            <h1 class="display text-2xl sm:text-[26px] tracking-[-0.025em]">Your login</h1>
            <p class="mt-1 text-[13.5px] text-ink-500">{{ $user->email }}</p>
        </x-page-band>
    @endif

    <x-notice :notice="$notice" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
        <div class="space-y-4">
            <form wire:submit="saveAccount" class="card p-6 space-y-5">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">Account</h2>
                    <p class="text-[13px] text-ink-500">Only you see this. Your public profile has its own name and photo.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="acc-name">Your name</label>
                        <input id="acc-name" type="text" wire:model="name" class="input">
                        <x-field-error for="name" />
                    </div>
                    <div>
                        <label class="label" for="acc-email">Email address</label>
                        <input id="acc-email" type="email" wire:model="email" class="input">
                        <p class="mt-1.5 text-xs text-ink-400">You sign in with this address and we send contact requests to it.</p>
                        <x-field-error for="email" />
                    </div>
                </div>
                <div class="flex justify-end"><button type="submit" class="btn-primary" wire:loading.attr="disabled">Save changes</button></div>
            </form>

            <form wire:submit="savePassword" class="card p-6 space-y-5">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">{{ $user->password ? 'Change password' : 'Set a password' }}</h2>
                    <p class="text-[13px] text-ink-500">
                        @if($user->password)
                            Changing it signs you out everywhere else.
                        @else
                            You sign in with Google right now. Setting a password gives you a second way in; Google keeps working.
                        @endif
                    </p>
                </div>
                @if($user->password)
                    <div class="sm:w-1/2">
                        <label class="label" for="pw-current">Current password</label>
                        <input id="pw-current" type="password" wire:model="current_password" class="input" autocomplete="current-password">
                        <x-field-error for="current_password" />
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="pw-new">New password</label>
                        <input id="pw-new" type="password" wire:model="password" class="input" autocomplete="new-password">
                        <p class="mt-1.5 text-xs text-ink-400">At least 8 characters.</p>
                        <x-field-error for="password" />
                    </div>
                    <div>
                        <label class="label" for="pw-confirm">Repeat new password</label>
                        <input id="pw-confirm" type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
                    </div>
                </div>
                <div class="flex justify-end"><button type="submit" class="btn-primary" wire:loading.attr="disabled">{{ $user->password ? 'Change password' : 'Set password' }}</button></div>
            </form>

            <details class="card !border-ink-100">
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-2 px-6 py-4 text-sm font-semibold text-ink-500">
                    Delete my account <span class="text-[13px] font-normal">Removes your login, profile and every connected platform. No undo.</span>
                </summary>
                <div class="border-t border-ink-100 px-6 py-5">@include('livewire.account.partials.delete-account', ['bare' => true])</div>
            </details>
        </div>

        <aside class="space-y-4">
            <div class="card p-5 space-y-3 text-[13.5px]">
                <h2 class="text-[15px] font-semibold text-ink-950">How you sign in</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Email</dt><dd class="font-medium text-ink-950 truncate">{{ $user->email }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Password</dt><dd class="font-medium text-ink-950">{{ $user->password ? 'Set' : 'Not set' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Google</dt><dd class="font-medium text-ink-950">{{ $user->google_id ? 'Connected' : 'Not connected' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">Member since</dt><dd class="font-medium text-ink-950">{{ $user->created_at->format('j M Y') }}</dd></div>
                </dl>
                @unless($user->google_id)
                    <a href="{{ route('login.google') }}" class="btn-secondary btn-sm w-full">Connect Google</a>
                @endunless
            </div>
            <div class="card p-5 text-[13.5px] text-ink-500">
                <p class="font-semibold text-ink-950">We never see your social passwords</p>
                <p class="mt-1">Connecting YouTube, Instagram or X happens on their own sign-in page. We only keep the access they hand back, and you can disconnect at any time.</p>
            </div>
        </aside>
    </div>
</div>
