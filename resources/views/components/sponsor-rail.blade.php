{{-- One of the two sponsor rails beside the page. Drawn only once the gutter beside the
     1152px container is wide enough to hold a card, so the container never moves. Every spot is
     shown: a booked one as its card, a free one as an invitation to take it. --}}
@props(['spots', 'side'])
@php
    use App\Support\Sponsorship;

    $price = Sponsorship::price();
    // With no price nothing is for sale, so a side with no cards has nothing to draw.
    $spots = $price ? $spots : $spots->filter(fn ($spot) => $spot['booking']?->isLive());
    // Nothing to show for a spot whose card is paused or finished but still on the booking.
    $anyOpen = $spots->contains(fn ($spot) => $spot['booking'] === null);
@endphp
@if($spots->isNotEmpty())
    <aside class="pointer-events-none absolute inset-y-0 {{ $side === 'left' ? 'left-0' : 'right-0' }} hidden w-[168px] min-[1440px]:block min-[1600px]:w-[216px] min-[1800px]:w-[240px]" aria-label="Sponsored">
        <div class="pointer-events-auto sticky top-6 flex flex-col gap-2.5 p-3 min-[1600px]:gap-3 min-[1600px]:p-4">
            @foreach($spots as $spot)
                @php($slot = $spot['booking']?->isLive() ? $spot['booking'] : null)
                @if($slot)
                    <a href="{{ route('sponsors.click', $slot) }}" target="_blank" rel="nofollow sponsored noopener"
                       class="block rounded-2xl border px-3 py-5 text-center transition-transform hover:-translate-y-0.5 min-[1600px]:px-4 min-[1600px]:py-6 {{ $slot->tintClasses() }}">
                        @if($slot->logo_url)
                            <img src="{{ $slot->logo_url }}" alt="" class="mx-auto size-9 rounded-lg object-cover" loading="lazy" onerror="this.remove()">
                        @else
                            <span class="mx-auto flex size-9 items-center justify-center rounded-lg bg-white/70 text-xs font-bold text-ink-700">{{ $slot->initials() }}</span>
                        @endif
                        <span class="mt-2 block text-[13px] font-semibold text-ink-950">{{ $slot->name }}</span>
                        <span class="mt-1 block text-[11.5px] leading-snug text-ink-600">{{ $slot->tagline }}</span>
                    </a>
                @elseif($spot['booking'])
                    {{-- Bought, but the buyer has not filled in their card yet. It is not for
                         sale and it is not a card either, so it says what it is. --}}
                    <div class="rounded-2xl border border-ink-200 bg-white/50 px-3 py-5 text-center min-[1600px]:px-4 min-[1600px]:py-6">
                        <span class="mx-auto flex size-9 items-center justify-center rounded-lg border border-ink-200 text-ink-300">
                            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V6.5a4 4 0 118 0V9M5 9h10v7H5z"/></svg>
                        </span>
                        <span class="mt-2 block text-[13px] font-semibold text-ink-500">Taken</span>
                        <span class="mt-1 block text-[11.5px] leading-snug text-ink-400">A card is on its way</span>
                    </div>
                @else
                    <a href="{{ route('sponsor', ['spot' => $spot['key']]) }}"
                       class="block rounded-2xl border border-dashed border-ink-400 px-3 py-5 text-center transition-colors hover:bg-white/60 min-[1600px]:px-4 min-[1600px]:py-6">
                        <span class="mx-auto flex size-9 items-center justify-center rounded-lg border border-dashed border-ink-400 text-ink-400">
                            <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 5v10M5 10h10"/></svg>
                        </span>
                        <span class="mt-2 block text-[13px] font-semibold text-brand-700">Open slot</span>
                        <span class="mt-1 block text-[11.5px] leading-snug text-ink-600 tnum">{{ $price }} / {{ Sponsorship::periodLabel() }}</span>
                        <span class="mt-1.5 block text-[11px] leading-snug text-ink-500">Put your product here</span>
                    </a>
                @endif
            @endforeach

            {{-- Nothing free: the way through to the queue, or the page is a dead end. --}}
            @if($price && ! $anyOpen)
                <a href="{{ route('sponsor') }}" class="block rounded-xl px-2 py-1.5 text-center text-[11px] font-semibold text-ink-500 transition-colors hover:text-brand-700">Sponsor this site &rarr;</a>
            @endif
        </div>
    </aside>
@endif
