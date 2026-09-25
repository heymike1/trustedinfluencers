{{-- One of the two sponsor rails beside the page. Drawn only once the gutter beside the
     1152px container is wide enough to hold a card, so the container never moves; hidden
     entirely when nothing is booked for that side. --}}
@props(['slots', 'side'])
@if($slots->isNotEmpty())
    <aside class="pointer-events-none absolute inset-y-0 {{ $side === 'left' ? 'left-0' : 'right-0' }} hidden w-[136px] min-[1440px]:block min-[1600px]:w-[184px]" aria-label="Sponsored">
        <div class="pointer-events-auto sticky top-6 flex flex-col gap-2.5 p-3 min-[1600px]:gap-3 min-[1600px]:p-4">
            @foreach($slots as $slot)
                <a href="{{ route('sponsors.click', $slot) }}" target="_blank" rel="nofollow sponsored noopener"
                   class="block rounded-2xl border p-3.5 text-center transition-transform hover:-translate-y-0.5 {{ $slot->tintClasses() }}">
                    @if($slot->logo_url)
                        <img src="{{ $slot->logo_url }}" alt="" class="mx-auto size-8 rounded-lg object-cover" loading="lazy" onerror="this.remove()">
                    @else
                        <span class="mx-auto flex size-8 items-center justify-center rounded-lg bg-white/70 text-[11px] font-bold text-ink-700">{{ $slot->initials() }}</span>
                    @endif
                    <span class="mt-2 block text-[13px] font-semibold text-ink-950">{{ $slot->name }}</span>
                    <span class="mt-1 block text-[11.5px] leading-snug text-ink-600">{{ $slot->tagline }}</span>
                </a>
            @endforeach
            <span class="text-center text-[10.5px] uppercase tracking-wide text-ink-400">Sponsored</span>
        </div>
    </aside>
@endif
