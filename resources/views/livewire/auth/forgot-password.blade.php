<div class="mx-auto max-w-sm">
    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Reset your password</h1>
    <p class="mt-1 text-sm text-ink-500">Tell us your email and we’ll send a link to choose a new one.</p>

    <div class="card mt-6 p-5">
        @if($sent)
            <p class="text-sm text-ink-900 font-medium">Check your inbox.</p>
            <p class="mt-1 text-sm text-ink-500">If there’s an account for that address, a reset link is on its way. It works for 60 minutes.</p>
        @else
            <form wire:submit="send" class="space-y-4">
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" wire:model="email" class="input" autocomplete="email" autofocus>
                    <x-field-error for="email" />
                </div>
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">Send reset link</button>
            </form>
        @endif
    </div>
    <p class="mt-4 text-sm text-ink-500">Signed up with Google? Just use <a href="{{ route('login') }}" class="text-ink-900 underline underline-offset-2">Continue with Google</a>, there’s no password to reset.</p>
</div>
