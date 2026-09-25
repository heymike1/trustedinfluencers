{{-- One of the two sponsor rails beside the page. Drawn only once the gutter beside the
     1152px container is wide enough to hold a card, so the container never moves. A side with
     nothing booked still shows the open slot card, unless the price is switched off. --}}
@props(['slots', 'side'])
@php
    // An open slot is offered until the rail holds the number of cards we sell per side.
    $price = config('social.sponsors.price');
    $openSlots = $price ? max(0, (int) config('social.sponsors.slots_per_rail') - $slots->count()) : 0;
    $contact = config('social.sponsors.contact');
@endphp
@if($slots->isNotEmpty() || $openSlots > 0)
    <aside class="pointer-events-none absolute inset-y-0 {{ $side === 'left' ? 'left-0' : 'right-0' }} hidden w-[168px] min-[1440px]:block min-[1600px]:w-[216px] min-[1800px]:w-[240px]" aria-label="Sponsored">
        <div class="pointer-events-auto sticky top-6 flex flex-col gap-2.5 p-3 min-[1600px]:gap-3 min-[1600px]:p-4">
            @foreach($slots as $slot)
                <a href="{{ route('sponsors.click', $slot) }}" target="_blank" rel="nofollow sponsored noopener"
                   class="block rounded-2xl border-2 px-3 py-5 text-center transition-transform hover:-translate-y-0.5 min-[1600px]:px-4 min-[1600px]:py-6 {{ $slot->tintClasses() }}">
                    @if($slot->logo_url)
                        <img src="{{ $slot->logo_url }}" alt="" class="mx-auto size-9 rounded-lg object-cover" loading="lazy" onerror="this.remove()">
                    @else
                        <span class="mx-auto flex size-9 items-center justify-center rounded-lg bg-white/70 text-xs font-bold text-ink-700">{{ $slot->initials() }}</span>
                    @endif
                    <span class="mt-2 block text-[13px] font-semibold text-ink-950">{{ $slot->name }}</span>
                    <span class="mt-1 block text-[11.5px] leading-snug text-ink-600">{{ $slot->tagline }}</span>
                </a>
            @endforeach

            @if($openSlots > 0)
                <a href="mailto:{{ $contact }}?subject={{ rawurlencode('Sponsoring '.config('app.name')) }}"
                   class="block rounded-2xl border-2 border-dashed border-ink-200 px-3 py-5 text-center transition-colors hover:bg-white/60 min-[1600px]:px-4 min-[1600px]:py-6">
                    <span class="mx-auto flex size-9 items-center justify-center rounded-lg border-2 border-dashed border-ink-200 text-ink-400">
                        <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 5v10M5 10h10"/></svg>
                    </span>
                    <span class="mt-2 block text-[13px] font-semibold text-brand-700">Open slot</span>
                    <span class="mt-1 block text-[11.5px] leading-snug text-ink-600 tnum">{{ $price }} / {{ config('social.sponsors.period') }}</span>
                    <span class="mt-1.5 block text-[11px] leading-snug text-ink-500">Put your product here</span>
                </a>
            @endif
        </div>
    </aside>
@endif
