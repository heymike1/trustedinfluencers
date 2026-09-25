<x-slot:heading>Sponsors</x-slot:heading>
<x-slot:subheading>Cards in the rails beside the public pages</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead><tr><th class="pl-4">Sponsor</th><th>Spot</th><th>Window</th><th class="text-right">Clicks</th><th>State</th><th class="pr-4"></th></tr></thead>
                <tbody>
                    @forelse($slots as $slot)
                        <tr wire:key="slot-{{ $slot->id }}">
                            <td class="pl-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg border text-[11px] font-bold text-ink-700 {{ $slot->tintClasses() }}">{{ $slot->initials() }}</span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-ink-950">{{ $slot->name ?? 'No card yet' }}</p>
                                        <p class="text-xs text-ink-500 truncate max-w-[260px]">{{ $slot->tagline ?? $slot->buyer_email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-ink-500 whitespace-nowrap">
                                {{ $slot->position ? ucfirst($slot->side).' '.$slot->position : 'In the queue' }}
                                @if($slot->amount)<span class="block text-ink-400 tnum">{{ \App\Support\Sponsorship::money($slot->amount) }} paid</span>@endif
                            </td>
                            <td class="text-xs text-ink-500 whitespace-nowrap tnum">
                                {{ $slot->starts_at?->format('j M Y') ?? '—' }} → {{ $slot->ends_at?->format('j M Y') ?? '—' }}
                            </td>
                            <td class="text-right tnum">{{ number_format($slot->clicks) }}</td>
                            <td><x-badge :variant="$slot->state() === 'Live' ? 'verified' : ($slot->state() === 'Paused' ? 'warn' : 'neutral')">{{ $slot->state() }}</x-badge></td>
                            <td class="pr-4 text-right whitespace-nowrap">
                                @if($slot->token)
                                    <a href="{{ route('sponsor.card', $slot->token) }}" target="_blank" class="btn-secondary btn-sm">Their page</a>
                                @endif
                                <button type="button" wire:click="edit({{ $slot->id }})" class="btn-secondary btn-sm">Edit</button>
                                <button type="button" wire:click="toggle({{ $slot->id }})" class="btn-secondary btn-sm">{{ $slot->is_active ? 'Pause' : 'Resume' }}</button>
                                @if(in_array($slot->status, [\App\Models\SponsorSlot::PAID, \App\Models\SponsorSlot::LIVE, \App\Models\SponsorSlot::PENDING], true))
                                    <button type="button" wire:click="cancelBooking({{ $slot->id }})" wire:confirm="Cancel this booking and free the spot?" class="btn-secondary btn-sm">Cancel</button>
                                @endif
                                <button type="button" wire:click="destroy({{ $slot->id }})" wire:confirm="Remove {{ $slot->name }} from the rails?" class="btn-danger btn-sm">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-10 text-center text-sm text-ink-500">Nothing booked yet. The rails offer every spot until someone takes one.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="space-y-5">
        <form wire:submit="saveSettings" class="card p-5 space-y-4">
            <div>
                <h2 class="text-[15px] font-semibold text-ink-950">Rail settings</h2>
                <p class="text-[13px] text-ink-500">What the rails offer, and what <a href="{{ route('sponsor') }}" target="_blank" class="font-semibold text-brand-700">/sponsor</a> tells a buyer.</p>
            </div>
            <p class="flex items-center gap-2 rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 text-[13px] text-ink-600">
                <span class="size-1.5 rounded-full {{ $open === 0 ? 'bg-ink-400' : 'bg-brand-600' }}"></span>
                {{ $open === 0 ? 'Sold out: buyers take the next spot that frees up.' : $open.' of '.$total.' spots open right now.' }}
                @if($queue->isNotEmpty())<span class="font-semibold text-ink-950">{{ $queue->count() }} paid and waiting.</span>@endif
            </p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="s-slots">Slots per side</label>
                    <input id="s-slots" type="number" min="0" max="12" wire:model="settings.slots_per_rail" class="input">
                    <p class="mt-1 text-xs text-ink-400">{{ $perRail * 2 }} in total. 0 hides the rails.</p>
                    <x-field-error for="settings.slots_per_rail" />
                </div>
                <div>
                    <label class="label" for="s-price">Price</label>
                    <input id="s-price" type="number" min="0" wire:model="settings.price" class="input" placeholder="250">
                    <p class="mt-1 text-xs text-ink-400">Whole units. 0 takes the spots off the market.</p>
                    <x-field-error for="settings.price" />
                </div>
                <div>
                    <label class="label" for="s-currency">Currency</label>
                    <select id="s-currency" wire:model="settings.currency" class="input">
                        <option value="eur">EUR €</option>
                        <option value="usd">USD $</option>
                        <option value="gbp">GBP £</option>
                    </select>
                    <x-field-error for="settings.currency" />
                </div>
                <div>
                    <label class="label" for="s-advance">Price when full</label>
                    <input id="s-advance" type="number" min="0" wire:model="settings.advance_price" class="input" placeholder="999">
                    <p class="mt-1 text-xs text-ink-400">Charged on <a href="{{ route('sponsor') }}" target="_blank" class="font-semibold text-brand-700">/sponsor</a> when every spot is taken and the buyer takes the next one that frees up.</p>
                    <x-field-error for="settings.advance_price" />
                </div>
                <div>
                    <label class="label" for="s-days">Days per booking</label>
                    <input id="s-days" type="number" min="1" max="365" wire:model="settings.days" class="input" placeholder="30">
                    <p class="mt-1 text-xs text-ink-400">A booking runs this long from the day the card goes up.</p>
                    <x-field-error for="settings.days" />
                </div>
                <div>
                    <label class="label" for="s-contact">Enquiries to</label>
                    <input id="s-contact" type="email" wire:model="settings.contact" class="input">
                    <x-field-error for="settings.contact" />
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="btn-primary" wire:loading.attr="disabled">Save settings</button></div>
        </form>

        <form wire:submit="save" class="card p-5 space-y-4">
            <div>
                <h2 class="text-[15px] font-semibold text-ink-950">{{ $editing ? 'Edit sponsor' : 'New sponsor' }}</h2>
                <p class="text-[13px] text-ink-500">Cards show beside the page on screens wider than 1440px. A new booking runs {{ $days }} days from today unless you move the dates.</p>
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
                    <label class="label" for="position">Spot</label>
                    <input id="position" type="number" min="1" max="{{ $perRail }}" wire:model="form.position" class="input" placeholder="1">
                    <p class="mt-1 text-xs text-ink-400">1 is the top card. Empty puts it in the queue.</p>
                    <x-field-error for="form.position" />
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
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-ink-400">Leave the dates empty to run until you pause it.</p>
                <button type="button" wire:click="bookFromToday" class="btn-secondary btn-sm">Book {{ $days }} days from today</button>
            </div>
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
</div>
