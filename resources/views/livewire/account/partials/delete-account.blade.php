<div class="card border-danger-100 p-5">
    <p class="text-sm font-semibold text-ink-950">Delete my account</p>
    <p class="mt-1 text-sm text-ink-500">
        This deletes your login{{ $creator ? ', your profile, every connected platform and everything we pulled in for them, contact requests included' : '' }}. There’s no undo.
        @if($creator)Just want out of the directory? Untick “Show my profile in the directory” above instead.@endif
    </p>
    <x-field-error for="account" />
    <div class="mt-3">
        <button type="button" wire:click="deleteAccount" wire:confirm.prompt="Delete your account{{ $creator ? ' and profile' : '' }}? This can’t be undone.\n\nType DELETE to confirm.|DELETE" class="btn-danger btn-sm">Delete my account</button>
    </div>
</div>
