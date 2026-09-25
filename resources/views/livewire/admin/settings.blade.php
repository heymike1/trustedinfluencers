<x-slot:heading>Settings</x-slot:heading>
<x-slot:subheading>Saved in the database and applied straight away. Credentials stay in .env.</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <form wire:submit="save" class="space-y-5">
            <section class="card p-5 space-y-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">Site</h2>
                    <p class="text-[13px] text-ink-500">The line under the name on the home page, and in the meta description.</p>
                </div>
                <div>
                    <label class="label" for="tagline">Tagline</label>
                    <input id="tagline" type="text" wire:model="form.tagline" class="input" maxlength="160">
                    <x-field-error for="form.tagline" />
                </div>
            </section>

            <section class="card p-5 space-y-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">Platforms</h2>
                    <p class="text-[13px] text-ink-500">A platform that is off keeps the data it already has, but cannot be added, claimed or connected and is offered nowhere.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($platforms as $platform)
                        @php($on = in_array($platform->value, $form['platforms'], true))
                        <button type="button" wire:click="togglePlatform('{{ $platform->value }}')" class="chip !py-2 {{ $on ? 'chip-on' : '' }}">
                            <x-platform-icon :platform="$platform" class="size-3.5" :colored="! $on" /> {{ $platform->label() }}
                            <span class="ml-0.5 text-[11px] {{ $on ? 'opacity-70' : 'text-ink-400' }}">{{ $on ? 'on' : 'off' }}</span>
                        </button>
                    @endforeach
                </div>
                <x-field-error for="form.platforms" />
            </section>

            <section class="card p-5 space-y-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">Syncing</h2>
                    <p class="text-[13px] text-ink-500">How often the scheduler pulls new numbers, and how much it keeps.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="refresh">Refresh every (hours)</label>
                        <input id="refresh" type="number" min="1" max="168" wire:model="form.refresh_every_hours" class="input">
                        <x-field-error for="form.refresh_every_hours" />
                    </div>
                    <div>
                        <label class="label" for="cooldown">Manual sync cooldown (minutes)</label>
                        <input id="cooldown" type="number" min="1" max="1440" wire:model="form.manual_cooldown_minutes" class="input">
                        <x-field-error for="form.manual_cooldown_minutes" />
                    </div>
                    <div>
                        <label class="label" for="stale">Numbers go stale after (days)</label>
                        <input id="stale" type="number" min="1" max="90" wire:model="form.stale_after_days" class="input">
                        <x-field-error for="form.stale_after_days" />
                    </div>
                    <div>
                        <label class="label" for="limit">Items imported per account</label>
                        <input id="limit" type="number" min="5" max="200" wire:model="form.content_limit" class="input">
                        <x-field-error for="form.content_limit" />
                    </div>
                </div>
            </section>

            <section class="card p-5 space-y-4">
                <div>
                    <h2 class="text-[15px] font-semibold text-ink-950">Contact requests</h2>
                </div>
                <x-switch-row model="form.forward_to_public_email" title="Forward messages for unclaimed profiles" description="Sends to the public address someone else typed in. Off by default, because that address is unverified." />
            </section>

            <div class="flex items-center justify-between gap-3">
                <button type="button" wire:click="resetToFile" wire:confirm="Drop every setting saved here and fall back to the config files?" class="btn-secondary">Reset to config files</button>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">Save settings</button>
            </div>
        </form>

        <aside class="card p-5">
            <h2 class="text-[15px] font-semibold text-ink-950">Set in .env</h2>
            <p class="text-[13px] text-ink-500">Credentials and infrastructure. Changing these needs a deploy.</p>
            <dl class="mt-3 space-y-2 text-[13px]">
                @foreach($fromEnv as $label => $value)
                    <div class="flex justify-between gap-3"><dt class="text-ink-500">{{ $label }}</dt><dd class="font-medium text-ink-950">{{ $value }}</dd></div>
                @endforeach
            </dl>
            <p class="mt-3 border-t border-ink-100 pt-3 text-xs text-ink-400">API keys for Google, Instagram and X are never shown here.</p>
        </aside>
    </div>
</div>
