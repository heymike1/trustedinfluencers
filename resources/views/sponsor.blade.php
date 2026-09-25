@php
    use App\Support\Sponsorship;

    $total = Sponsorship::total();
    $open = Sponsorship::open();
    $forSale = Sponsorship::isForSale();
    $full = $forSale && Sponsorship::isFull();
    $price = Sponsorship::price();
    $advance = Sponsorship::advancePrice();
    $days = Sponsorship::days();
    $nextFree = Sponsorship::nextFreeAt();
    $mailto = Sponsorship::mailto($full ? 'The next spot on' : 'Booking a spot on');
@endphp
<x-layouts.app title="Sponsor" description="One card in the rails beside every page of {{ config('app.name') }}, for {{ $days }} days. {{ $total }} spots in total, booked one month at a time." :canonical="route('sponsor')">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-12 sm:pt-16 sm:pb-16 flex flex-col gap-6">
                <span class="eyebrow self-start">Sponsor {{ config('app.name') }}</span>
                <h1 class="display max-w-3xl text-3xl leading-[1.1] sm:text-4xl lg:text-[40px]">{{ $total }} spots, beside the creators brands are already looking at.</h1>
                <p class="max-w-2xl text-base text-ink-700 sm:text-lg text-pretty">The cards in the rails on either side of this page are the only advertising on the site. One card is yours for {{ $days }} days and it runs everywhere a visitor on a wide screen goes: the home page, the directory, the leaderboard and every creator profile.</p>

                <div class="flex flex-wrap items-center gap-3">
                    @if(! $forSale)
                        <a href="mailto:{{ Sponsorship::contact() }}" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">Ask when spots come back</a>
                    @elseif($full)
                        <a href="{{ $mailto }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Take the next spot @if($advance)<span class="tnum">· {{ $advance }}</span>@endif</a>
                    @else
                        <a href="{{ $mailto }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Book a spot <span class="tnum">· {{ $price }} / {{ $days }} days</span></a>
                    @endif
                    <a href="#how" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">How it works</a>
                </div>

                @if(! $forSale)
                    <p class="max-w-2xl text-[13px] text-ink-500">We have taken the spots off the market for now. Mail <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a> and we will let you know when they are back.</p>
                @else
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1.5 text-[13px] text-ink-500">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-band-edge bg-white px-2.5 py-1 font-semibold {{ $full ? 'text-ink-700' : 'text-brand-700' }}">
                            <span class="size-1.5 rounded-full {{ $full ? 'bg-ink-400' : 'bg-brand-600' }}"></span>
                            {{ $full ? 'All '.$total.' spots are taken' : $open.' of '.$total.' spots open' }}
                        </span>
                        @if($full)
                            <span>
                                You can still buy one: you take the first spot that comes free.
                                @if($nextFree)
                                    That is <span class="font-semibold text-ink-950">{{ $nextFree->format('j F Y') }}</span>.
                                @endif
                                Pay first, go first. Your {{ $days }} days start the day your card goes up.
                            </span>
                        @else
                            <span>Booked per month, {{ $days }} days from the day your card goes live. No auto-renew.</span>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </x-slot:hero>

    {{-- The whole deal in three steps. Everything a buyer needs to know is in here. --}}
    <section id="how" class="scroll-mt-24 mb-4">
        <h2 class="display text-2xl sm:text-[28px]">How it works</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Add your product or website', 'Click any open slot on the site and you land here. You need a name, one line about what you make, and a link. A logo if you have one; if you don’t, we use your initials. That is the whole card, so there is nothing to design.'],
                ['Pick your spot and pay for the month', 'Say which side you want, left or right. Whatever this page calls open is genuinely open, and if everything is taken you simply take the first spot that frees up. '.($price ?: 'The price').' covers '.$days.' days, one invoice, and the card goes up as soon as it is paid.'],
                ['It runs '.$days.' days, then it is free again', 'The clock starts the day your card appears, not the day you paid. Nothing renews behind your back: when the '.$days.' days are up the card comes down and the spot goes back on the page for the next one.'],
            ] as $i => [$title, $body])
                <div class="card p-5">
                    <span class="flex size-7 items-center justify-center rounded-full bg-brand-100 text-[13px] font-bold text-brand-700 tnum">{{ $i + 1 }}</span>
                    <p class="mt-3 font-semibold text-ink-950">{{ $title }}</p>
                    <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink-600">{{ $body }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-5 text-[13.5px] text-ink-500">Questions first: <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a>.</p>
    </section>
</x-layouts.app>
