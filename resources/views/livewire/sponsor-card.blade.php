@php
    use App\Support\Sponsorship;
    $ordinal = fn (int $n) => $n.([1 => 'st', 2 => 'nd', 3 => 'rd'][$n] ?? 'th');
@endphp
<div>
    <x-page-band>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="eyebrow">Your sponsor card</span>
                <h1 class="display mt-3 text-2xl sm:text-3xl">
                    @if($booking->isLive())
                        Your card is up.
                    @elseif($booking->isQueued())
                        You are {{ $ahead === 0 ? 'next in line' : 'number '.($ahead + 1).' in line' }}.
                    @else
                        Tell us what goes in your spot.
                    @endif
                </h1>
                <p class="mt-1.5 text-[14px] text-ink-600">
                    @if($booking->isLive())
                        {{ ucfirst($booking->side) }} rail, {{ $ordinal($booking->position) }} card. Expires on {{ $booking->ends_at?->format('j F Y') }}. No auto-renew.
                    @elseif($booking->isQueued())
                        Everything was taken when you paid, so you take the first spot that comes free. Fill your card in now and it goes up the moment there is room.
                    @elseif($booking->needsDetails())
                        {{ ucfirst($booking->side) }} rail, {{ $ordinal($booking->position) }} card. It goes up as soon as this is complete, and your {{ $days }} days start then.
                    @else
                        This booking is no longer running.
                    @endif
                </p>
            </div>
        </div>
    </x-page-band>

    @if($booking->isQueued())
        <div class="card mb-6 flex flex-wrap items-center justify-between gap-3 px-5 py-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Your spot is visible from</p>
                <p class="display mt-1 text-2xl">{{ $freedBy?->ends_at?->format('j F Y') ?? 'the first card that comes down' }}</p>
            </div>
            <p class="max-w-sm text-[13px] leading-relaxed text-ink-600">
                @if($freedBy)
                    {{ $freedBy->name }} runs out that day in the {{ $freedBy->side }} rail, {{ $ordinal($freedBy->position) }} card. Yours takes that place and we email you when it is up.
                @else
                    We email you the moment yours is up.
                @endif
            </p>
        </div>
    @endif

    <x-flash />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <form wire:submit="save" class="card p-6 space-y-5">
            <div>
                <h2 class="text-[15px] font-semibold text-ink-950">The card</h2>
                <p class="text-[13px] text-ink-500">Three fields and a colour. Change them whenever you like; the card updates straight away.</p>
            </div>

            <div>
                <label class="label" for="url">Your website</label>
                <input id="url" type="url" wire:model.live.debounce.500ms="url" class="input" placeholder="https://yourproduct.com">
                <p class="mt-1.5 text-xs text-ink-400">Your logo comes from this domain, so there is nothing to upload.</p>
                <x-field-error for="url" />
            </div>

            <div>
                <label class="label" for="name">Name</label>
                <input id="name" type="text" wire:model.live.debounce.400ms="name" class="input" maxlength="60" placeholder="Your product">
                <x-field-error for="name" />
            </div>

            <div>
                <label class="label" for="tagline">One line</label>
                <textarea id="tagline" rows="2" wire:model.live.debounce.400ms="tagline" class="input" maxlength="120" placeholder="What it does, in a handful of words"></textarea>
                <p class="mt-1.5 text-xs text-ink-400">Under about 60 characters keeps the card compact.</p>
                <x-field-error for="tagline" />
            </div>

            <div>
                <span class="label">Card colour</span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($tints as $option)
                        <button type="button" wire:click="$set('tint', '{{ $option }}')" class="size-8 rounded-lg border-2 {{ \App\Models\SponsorSlot::TINTS[$option] }} {{ $tint === $option ? 'ring-2 ring-brand-700 ring-offset-2' : '' }}" title="{{ ucfirst($option) }}"></button>
                    @endforeach
                </div>
                <x-field-error for="tint" />
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-ink-100 pt-4">
                <span class="text-[13px] text-ink-500"><span wire:dirty>Not saved yet</span><span wire:dirty.remove>{{ $booking->isLive() ? 'Live on the site' : 'Saved' }}</span></span>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">{{ $booking->isLive() ? 'Save changes' : 'Put my card up' }}</button>
            </div>
        </form>

        <aside class="space-y-4">
            {{-- Exactly what the rails will show. --}}
            <div class="card p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Preview</p>
                <div class="mt-3 rounded-2xl border px-4 py-6 text-center {{ \App\Models\SponsorSlot::TINTS[$tint] ?? '' }}">
                    @if($logo_url)
                        <img src="{{ $logo_url }}" alt="" class="mx-auto size-9 rounded-lg object-cover" onerror="this.remove()">
                    @else
                        <span class="mx-auto flex size-9 items-center justify-center rounded-lg bg-white/70 text-xs font-bold text-ink-700">{{ mb_strtoupper(mb_substr($name ?: 'Yo', 0, 2)) }}</span>
                    @endif
                    <span class="mt-2 block text-[13px] font-semibold text-ink-950">{{ $name ?: 'Your product' }}</span>
                    <span class="mt-1 block text-[11.5px] leading-snug text-ink-600">{{ $tagline ?: 'One line about what it does' }}</span>
                </div>
            </div>

            <div class="card p-5 text-[13px] leading-relaxed text-ink-600 space-y-2">
                <p class="font-semibold text-ink-950">Good to know</p>
                <p>Keep this page bookmarked: it is the only way back in. Lost it? Mail <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a>.</p>
            </div>
        </aside>
    </div>
</div>
