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
    $bookMail = Sponsorship::mailto('Booking a spot on');
    $advanceMail = Sponsorship::mailto('Advance for the next spot on');
@endphp
<x-layouts.app title="Sponsor" description="One card in the rails beside every page of {{ config('app.name') }}, for {{ $days }} days. {{ $total }} spots in total, booked one month at a time." :canonical="route('sponsor')">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-12 sm:pt-16 sm:pb-16 flex flex-col gap-6">
                <span class="eyebrow self-start">Sponsor {{ config('app.name') }}</span>
                <h1 class="display max-w-3xl text-3xl leading-[1.1] sm:text-4xl lg:text-[40px]">{{ $total }} spots, beside the creators brands are already looking at.</h1>
                <p class="max-w-2xl text-base text-ink-700 sm:text-lg text-pretty">The cards in the rails on either side of this page are the only advertising on the site. One card is yours for {{ $days }} days and it runs everywhere: the home page, the directory, the leaderboard and every creator profile.</p>

                <div class="flex flex-wrap items-center gap-3">
                    @if(! $forSale)
                        <span class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge pointer-events-none">Spots are not for sale right now</span>
                    @elseif($full)
                        <a href="{{ $advanceMail }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Reserve the next spot @if($advance)<span class="tnum">· {{ $advance }}</span>@endif</a>
                        <a href="#availability" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">How the waiting list works</a>
                    @else
                        <a href="{{ $bookMail }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Book a spot <span class="tnum">· {{ $price }} / {{ $days }} days</span></a>
                        <a href="#how" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">How it works</a>
                    @endif
                </div>

                @if($forSale)
                    <p class="flex flex-wrap items-center gap-2 text-[13px] text-ink-500">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-band-edge bg-white px-2.5 py-1 font-semibold {{ $full ? 'text-ink-700' : 'text-brand-700' }}">
                            <span class="size-1.5 rounded-full {{ $full ? 'bg-ink-400' : 'bg-brand-600' }}"></span>
                            {{ $full ? 'All '.$total.' spots are taken' : $open.' of '.$total.' spots open' }}
                        </span>
                        <span>Booked per month, {{ $days }} days from the day your card goes live. No auto-renew.</span>
                    </p>
                @endif
            </div>
        </div>
    </x-slot:hero>

    {{-- How it works. Three steps, because that is all there is to it while we invoice by hand. --}}
    <section id="how" class="scroll-mt-24">
        <h2 class="display text-2xl sm:text-[28px]">How it works</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Tell us which side', 'Email us and say left or right. We reply with what is free and hold it for you while we sort out the invoice.'],
                ['Send your card', 'A logo, your name, one line about the product and the link. That is the whole card; there is no design work on your end.'],
                ['Live for '.$days.' days', 'The clock starts the day it goes up, not the day you pay. When the '.$days.' days are over the card comes down by itself and the spot goes back on the page.'],
            ] as $i => [$title, $body])
                <div class="card p-5">
                    <span class="flex size-7 items-center justify-center rounded-full bg-brand-100 text-[13px] font-bold text-brand-700 tnum">{{ $i + 1 }}</span>
                    <p class="mt-3 font-semibold text-ink-950">{{ $title }}</p>
                    <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink-600">{{ $body }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- What the spot actually is. Only things a buyer can check for themselves. --}}
    <section class="mt-10">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
            <div class="card p-6">
                <h2 class="display text-2xl sm:text-[28px]">What a spot is</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach([
                        ['On every page', 'The rails sit beside the home page, the directory, the leaderboard and all creator profiles. Not a banner that rotates: your card is where you put it, all month.'],
                        ['Beside the numbers', 'People come here to check what a creator really pulls in before they pay them. Your card is next to that decision.'],
                        ['Yours alone', 'There are '.$total.' spots on the whole site and we do not sell more. No pop-ups, no newsletter inserts, no sponsored profiles.'],
                        ['Clicks, counted', 'We count every click on your card and tell you the number whenever you ask. Links carry rel="sponsored nofollow", the way they should.'],
                    ] as [$title, $body])
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                                <svg class="size-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-semibold text-ink-950">{{ $title }}</span>
                                <span class="mt-0.5 block text-[13px] leading-relaxed text-ink-600">{{ $body }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- A card exactly as it appears in the rail, so nobody has to guess. --}}
            <aside class="card p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-ink-500">Your card</p>
                <div class="mt-3 rounded-2xl border border-[#a8c4ea] bg-[#e8f0fc] px-4 py-6 text-center">
                    <span class="mx-auto flex size-9 items-center justify-center rounded-lg bg-white/70 text-xs font-bold text-ink-700">YO</span>
                    <span class="mt-2 block text-[13px] font-semibold text-ink-950">Your product</span>
                    <span class="mt-1 block text-[11.5px] leading-snug text-ink-600">One line about what it does</span>
                </div>
                <p class="mt-3 text-[13px] leading-relaxed text-ink-600">Six card colours to pick from, so yours does not have to look like the one above it.</p>
            </aside>
        </div>
    </section>

    {{-- Availability: the one block that changes depending on what is left. --}}
    <section id="availability" class="mt-10 scroll-mt-24">
        @if(! $forSale)
            <div class="card p-6">
                <h2 class="display text-2xl sm:text-[28px]">Not for sale at the moment</h2>
                <p class="mt-2 max-w-2xl text-[15px] leading-relaxed text-ink-600">We have taken the spots off the market for now. Mail <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a> and we will let you know when they are back.</p>
            </div>
        @elseif($full)
            <div class="card overflow-hidden">
                <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700"><span class="size-1.5 rounded-full bg-ink-400"></span> Fully booked</span>
                        <h2 class="display mt-3 text-2xl sm:text-[28px]">Every spot is taken this month.</h2>
                        <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-ink-600">
                            All {{ $total }} cards are running, and we do not add extras.
                            @if($nextFree)
                                The first one comes free on <span class="font-semibold text-ink-950">{{ $nextFree->format('j F Y') }}</span>.
                            @else
                                The moment one ends, it is offered again on this page.
                            @endif
                        </p>
                        <p class="mt-3 max-w-xl text-[15px] leading-relaxed text-ink-600">
                            @if($advance)
                                Put down an advance of <span class="font-semibold text-ink-950 tnum">{{ $advance }}</span> and the next spot that frees up is yours before it goes back on the page. The advance covers your first {{ $days }} days; if we cannot place you within three months, you get it back in full.
                            @else
                                Leave your details and we will come to you the moment a spot frees up, in the order the requests came in.
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col gap-2.5 rounded-2xl border border-ink-200 bg-ink-50 p-5">
                        @if($advance)
                            <p class="display text-3xl tnum">{{ $advance }}</p>
                            <p class="text-[13px] text-ink-600">Advance for the next opening. Covers {{ $days }} days once your card is live.</p>
                        @else
                            <p class="font-semibold text-ink-950">Join the list</p>
                            <p class="text-[13px] text-ink-600">First in, first served.</p>
                        @endif
                        <a href="{{ $advanceMail }}" class="btn-primary mt-1 w-full">{{ $advance ? 'Reserve the next spot' : 'Put me on the list' }}</a>
                        <p class="text-[11.5px] leading-snug text-ink-500">We invoice by email and confirm the date in writing before you pay a cent.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="card overflow-hidden">
                <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-band-edge bg-white px-2.5 py-1 text-xs font-semibold text-brand-700"><span class="size-1.5 rounded-full bg-brand-600"></span> {{ $open }} of {{ $total }} open</span>
                        <h2 class="display mt-3 text-2xl sm:text-[28px]">{{ $open === 1 ? 'One spot is still open.' : 'There is room this month.' }}</h2>
                        <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-ink-600">Tell us which side you want and what the card should say. We put it up the same day and it runs {{ $days }} days from there. Nothing renews by itself: we ask before the month is up, and if you leave it, the card simply comes down.</p>
                    </div>
                    <div class="flex flex-col gap-2.5 rounded-2xl border border-ink-200 bg-ink-50 p-5">
                        <p class="display text-3xl tnum">{{ $price }}</p>
                        <p class="text-[13px] text-ink-600">Per card, per {{ $days }} days. One invoice, no percentages.</p>
                        <a href="{{ $bookMail }}" class="btn-primary mt-1 w-full">Book a spot</a>
                        <p class="text-[11.5px] leading-snug text-ink-500">Card payments are on the way. For now we send an invoice by email.</p>
                    </div>
                </div>
            </div>
        @endif
    </section>

    {{-- The things people email us about anyway. --}}
    <section class="mt-10 mb-4">
        <h2 class="display text-2xl sm:text-[28px]">Good to know</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach([
                ['Who sees the rails?', 'Visitors on a wide screen. The rails need room beside the page, so they appear from about 1440 pixels and stay hidden on phones and narrow laptops. Your card is for the people browsing from a desk.'],
                ['What can I advertise?', 'Anything a creator or a brand would genuinely use. We say no to anything misleading, to follower shops and to engagement farms, since that is the exact thing this site exists to undo.'],
                ['Can I change the card?', 'Yes, mail us and we will swap the logo, the line or the link during the run. Same spot, same end date.'],
                ['How do I pay?', 'An invoice by email, payable before the card goes live. Card checkout is coming; until then this is the whole process.'],
            ] as [$q, $a])
                <div class="card p-5">
                    <p class="font-semibold text-ink-950">{{ $q }}</p>
                    <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink-600">{{ $a }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-5 text-[13.5px] text-ink-500">Anything else: <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a>.</p>
    </section>
</x-layouts.app>
