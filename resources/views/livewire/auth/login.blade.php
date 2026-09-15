<div class="mx-auto max-w-sm">
    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Sign in</h1>
    <p class="mt-1 text-sm text-ink-500">Sign in to claim your profile or manage your connected accounts.</p>

    <div class="card mt-6 p-5">
        <x-google-button />
        <div class="my-4 flex items-center gap-3 text-xs text-ink-400"><span class="h-px flex-1 bg-ink-200"></span>or use email<span class="h-px flex-1 bg-ink-200"></span></div>
        <form wire:submit="login" class="space-y-4">
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" type="email" wire:model="email" class="input" autocomplete="email">
                <x-field-error for="email" />
            </div>
            <div>
                <div class="flex items-baseline justify-between"><label class="label" for="password">Password</label><a href="{{ route('password.request') }}" class="text-xs text-ink-500 hover:text-ink-950">Forgot it?</a></div>
                <input id="password" type="password" wire:model="password" class="input" autocomplete="current-password">
                <x-field-error for="password" />
            </div>
            <label class="flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" wire:model="remember" class="accent-brand-600"> Remember me</label>
            <button type="submit" class="btn-secondary w-full" wire:loading.attr="disabled">Sign in with email</button>
        </form>
    </div>
    <p class="mt-4 text-sm text-ink-500">No account yet? <a href="{{ route('register') }}" class="text-ink-900 underline underline-offset-2">Create one</a>.</p>
</div>
