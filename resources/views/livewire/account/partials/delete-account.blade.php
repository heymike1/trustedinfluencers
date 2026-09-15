{{-- `bare` drops the card wrapper and heading, for use inside a collapsed section. --}}
@php($bare = $bare ?? false)
<div class="{{ $bare ? '' : 'card border-danger-100 p-5' }}">
    @unless($bare)<p class="text-sm font-semibold text-ink-950">Delete my account</p>@endunless
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="max-w-xl text-[13px] text-ink-500 {{ $bare ? '' : 'mt-1' }}">
            This deletes your login{{ $creator ? ', your profile, every connected platform and everything we pulled in for them, contact requests included' : '' }}. There’s no undo.
            @if($creator)Just want out of the directory? Use the Visibility switch above instead.@endif
        </p>
        <button type="button" wire:click="deleteAccount" wire:confirm.prompt="Delete your account{{ $creator ? ' and profile' : '' }}? This can’t be undone.\n\nType DELETE to confirm.|DELETE" class="btn-danger btn-sm">Delete my account…</button>
    </div>
    <x-field-error for="account" />
</div>
