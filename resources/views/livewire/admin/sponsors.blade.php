<x-slot:heading>Sponsors</x-slot:heading>
<x-slot:subheading>Cards in the rails beside the public pages</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead><tr><th class="pl-4">Sponsor</th><th>Side</th><th class="text-right">Order</th><th>Window</th><th class="text-right">Clicks</th><th>State</th><th class="pr-4"></th></tr></thead>
                <tbody>
                    @forelse($slots as $slot)
                        <tr wire:key="slot-{{ $slot->id }}">
                            <td class="pl-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg border text-[11px] font-bold text-ink-700 {{ $slot->tintClasses() }}">{{ $slot->initials() }}</span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-ink-950">{{ $slot->name }}</p>
                                        <p class="text-xs text-ink-500 truncate max-w-[260px]">{{ $slot->tagline }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-ink-500 capitalize">{{ $slot->side }}</td>
                            <td class="text-right tnum">{{ $slot->sort_order }}</td>
                            <td class="text-xs text-ink-500 whitespace-nowrap tnum">
                                {{ $slot->starts_at?->format('j M Y') ?? '—' }} → {{ $slot->ends_at?->format('j M Y') ?? '—' }}
                            </td>
                            <td class="text-right tnum">{{ number_format($slot->clicks) }}</td>
                            <td><x-badge :variant="$slot->state() === 'Live' ? 'verified' : ($slot->state() === 'Paused' ? 'warn' : 'neutral')">{{ $slot->state() }}</x-badge></td>
                            <td class="pr-4 text-right whitespace-nowrap">
                                <button type="button" wire:click="edit({{ $slot->id }})" class="btn-secondary btn-sm">Edit</button>
                                <button type="button" wire:click="toggle({{ $slot->id }})" class="btn-secondary btn-sm">{{ $slot->is_active ? 'Pause' : 'Resume' }}</button>
                                <button type="button" wire:click="destroy({{ $slot->id }})" wire:confirm="Remove {{ $slot->name }} from the rails?" class="btn-danger btn-sm">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-10 text-center text-sm text-ink-500">No sponsors booked yet. The rails stay hidden until you add one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form wire:submit="save" class="card p-5 space-y-4">
            <div>
                <h2 class="text-[15px] font-semibold text-ink-950">{{ $editing ? 'Edit sponsor' : 'New sponsor' }}</h2>
                <p class="text-[13px] text-ink-500">Cards show beside the page on screens wider than 1536px, newest booking last.</p>
            </div>

            <div>
                <label class="label" for="name">Name</label>
                <input id="name" type="text" wire:model="form.name" class="input" maxlength="60">
                <x-field-error for="form.name" />
            </div>
            <div>
                <label class="label" for="tagline">One line</label>
                <textarea id="tagline" rows="2" wire:model="form.tagline" class="input" maxlength="120"></textarea>
                <p class="mt-1 text-xs text-ink-400">Keep it under about 60 characters or the card grows tall.</p>
                <x-field-error for="form.tagline" />
            </div>
            <div>
                <label class="label" for="url">Link</label>
                <input id="url" type="url" wire:model="form.url" class="input" placeholder="https://">
                <x-field-error for="form.url" />
            </div>
            <div>
                <label class="label" for="logo_url">Logo URL <span class="normal-case font-normal text-ink-400">(optional)</span></label>
                <input id="logo_url" type="url" wire:model="form.logo_url" class="input" placeholder="https://">
                <p class="mt-1 text-xs text-ink-400">Square image. Without one we show the initials.</p>
                <x-field-error for="form.logo_url" />
            </div>
            <div>
                <span class="label">Card colour</span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($tints as $tint)
                        <button type="button" wire:click="$set('form.tint', '{{ $tint }}')" class="size-8 rounded-lg border-2 {{ \App\Models\SponsorSlot::TINTS[$tint] }} {{ $form['tint'] === $tint ? 'ring-2 ring-brand-700 ring-offset-2' : '' }}" title="{{ ucfirst($tint) }}"></button>
                    @endforeach
                </div>
                <x-field-error for="form.tint" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="side">Side</label>
                    <select id="side" wire:model="form.side" class="input">
                        <option value="left">Left</option>
                        <option value="right">Right</option>
                    </select>
                </div>
                <div>
                    <label class="label" for="sort_order">Order</label>
                    <input id="sort_order" type="number" min="0" max="999" wire:model="form.sort_order" class="input">
                    <x-field-error for="form.sort_order" />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="starts_at">Starts</label>
                    <input id="starts_at" type="date" wire:model="form.starts_at" class="input">
                    <x-field-error for="form.starts_at" />
                </div>
                <div>
                    <label class="label" for="ends_at">Ends</label>
                    <input id="ends_at" type="date" wire:model="form.ends_at" class="input">
                    <x-field-error for="form.ends_at" />
                </div>
            </div>
            <p class="text-xs text-ink-400">Leave the dates empty to run until you pause it.</p>
            <x-switch-row model="form.is_active" title="Active" description="A paused sponsor keeps its booking but disappears from the rails." />

            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">{{ $editing ? 'Save changes' : 'Add sponsor' }}</button>
                @if($editing)
                    <button type="button" wire:click="cancel" class="btn-secondary">Cancel</button>
                @endif
            </div>
        </form>
    </div>
</div>
