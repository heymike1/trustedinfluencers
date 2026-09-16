<div class="mx-auto max-w-sm">
    <h1 class="display text-2xl tracking-[-0.025em]">Create account</h1>
    <p class="mt-1 text-sm text-ink-500">You only need an account to claim a creator profile. Anyone can browse and add creators without one.</p>

    <div class="card mt-6 p-5">
        <x-google-button label="Sign up with Google" />
        <p class="mt-2 text-xs text-ink-400">Google only tells us your name and email. Connecting YouTube is a separate step on your profile.</p>
        <div class="my-4 flex items-center gap-3 text-xs text-ink-400"><span class="h-px flex-1 bg-ink-200"></span>or use email<span class="h-px flex-1 bg-ink-200"></span></div>
        <form wire:submit="register" class="space-y-4">
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" type="text" wire:model="name" class="input" autocomplete="name">
                <x-field-error for="name" />
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" wire:model="email" class="input" autocomplete="email">
                <x-field-error for="email" />
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" wire:model="password" class="input" autocomplete="new-password">
                <x-field-error for="password" />
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
            </div>
            <button type="submit" class="btn-secondary w-full" wire:loading.attr="disabled">Create account with email</button>
        </form>
    </div>
    <p class="mt-4 text-sm text-ink-500">Already have an account? <a href="{{ route('login') }}" class="text-ink-900 underline underline-offset-2">Sign in</a>.</p>
</div>
