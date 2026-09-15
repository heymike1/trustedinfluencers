<div>
    @if(! $creator)
        <x-no-creator />
        <div class="mx-auto max-w-lg mt-8">@include('livewire.account.partials.delete-account')</div>
    @else
        <x-account-nav :creator="$creator" />

        <div class="grid gap-8 lg:grid-cols-[1fr_300px]">
            <form wire:submit="save" class="card p-5 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label" for="name">Display name</label>
                        <input id="name" type="text" wire:model="name" class="input">
                        <x-field-error for="name" />
                    </div>
                    <div>
                        <label class="label" for="category">Category</label>
                        <select id="category" wire:model="category" class="input">
                            <option value="">None</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <x-field-error for="category" />
                    </div>
                </div>
                <div>
                    <label class="label" for="bio">Bio</label>
                    <textarea id="bio" rows="3" wire:model="bio" class="input" maxlength="500"></textarea>
                    <x-field-error for="bio" />
                </div>
                <div>
                    <label class="label" for="avatar_url">Profile photo URL</label>
                    <input id="avatar_url" type="url" wire:model="avatar_url" class="input" placeholder="https://…">
                    <p class="mt-1 text-xs text-ink-400">Leave it empty and we’ll use the photo from your connected account.</p>
                    <x-field-error for="avatar_url" />
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="label" for="location">Location</label>
                        <input id="location" type="text" wire:model="location" class="input">
                        <x-field-error for="location" />
                    </div>
                    <div>
                        <label class="label" for="website">Website</label>
                        <input id="website" type="url" wire:model="website" class="input" placeholder="https://…">
                        <x-field-error for="website" />
                    </div>
                </div>
                <div class="border-t border-ink-100 pt-5 space-y-4">
                    <div>
                        <label class="label" for="contact_email">Contact email <span class="normal-case font-normal text-ink-400">(optional, never shown publicly)</span></label>
                        <input id="contact_email" type="email" wire:model="contact_email" class="input" placeholder="Defaults to your login email">
                        <p class="mt-1 text-xs text-ink-400">Contact requests go here. Nobody sees the address itself.</p>
                        <x-field-error for="contact_email" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-ink-900"><input type="checkbox" wire:model="contact_enabled" class="accent-brand-600"> Accept contact requests through my profile</label>
                </div>
                <div class="border-t border-ink-100 pt-5">
                    <label class="flex items-center gap-2 text-sm text-ink-900"><input type="checkbox" wire:model="is_listed" class="accent-brand-600"> Show my profile in the directory</label>
                    <p class="mt-1 text-xs text-ink-400">Turn this off and your profile disappears from the directory, rankings and search engines. Only you can still open it. Your data stays as it is, so you can turn it back on any time.</p>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">Save changes</button>
                </div>
            </form>

            <aside class="text-sm text-ink-500 space-y-3">
                <p class="font-medium text-ink-900">What you can’t edit</p>
                <p>Your verified numbers, follower count and content stats come straight from the platform, so there’s nothing to edit here. To refresh or reconnect, go to <a href="{{ route('account.connections') }}" class="underline underline-offset-2 text-ink-900">Connected accounts</a>.</p>
            </aside>
        </div>

        <div class="mt-8 lg:max-w-[calc(100%-332px)]">@include('livewire.account.partials.delete-account')</div>
    @endif
</div>
