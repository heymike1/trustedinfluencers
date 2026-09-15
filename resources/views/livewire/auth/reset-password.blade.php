<div class="mx-auto max-w-sm">
    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Choose a new password</h1>

    <form wire:submit="save" class="card mt-6 p-5 space-y-4">
        <div>
            <label class="label" for="email">Email</label>
            <input id="email" type="email" wire:model="email" class="input" autocomplete="email">
            <x-field-error for="email" />
        </div>
        <div>
            <label class="label" for="password">New password</label>
            <input id="password" type="password" wire:model="password" class="input" autocomplete="new-password" autofocus>
            <x-field-error for="password" />
        </div>
        <div>
            <label class="label" for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" wire:model="password_confirmation" class="input" autocomplete="new-password">
        </div>
        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">Save password</button>
    </form>
</div>
