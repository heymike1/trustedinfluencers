@php
    use App\Support\Sponsorship;

    $total = Sponsorship::total();
    $open = Sponsorship::open();
    $forSale = Sponsorship::isForSale();
    $full = $forSale && Sponsorship::isFull();
    $price = Sponsorship::price();
    $days = Sponsorship::days();
    $nextFree = Sponsorship::nextFreeAt();
    $queue = Sponsorship::queueLength();

    // Which spot they clicked, and whether it is still there.
    $asked = request('spot');
    $spot = Sponsorship::parseSpot($asked);
    $spotTaken = $spot && ! Sponsorship::isSpotOpen($asked);
    $spot = $spotTaken ? null : $spot;
    $due = Sponsorship::money($spot ? Sponsorship::amount() : Sponsorship::amountDueNow());
    $ordinal = fn (int $n) => $n.([1 => 'st', 2 => 'nd', 3 => 'rd'][$n] ?? 'th');
@endphp
<x-layouts.app title="Sponsor" description="One card in the rails beside every page of {{ config('app.name') }}, for {{ $days }} days. {{ $total }} spots in total, booked one month at a time." :canonical="route('sponsor')">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-12 sm:pt-16 sm:pb-16">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:items-start">
                    <div class="flex flex-col gap-5">
                        <span class="eyebrow self-start">Sponsor {{ config('app.name') }}</span>
                        <h1 class="display max-w-2xl text-3xl leading-[1.1] sm:text-4xl lg:text-[40px]">
                            @if($spot)
                                The {{ $ordinal($spot[1]) }} card in the {{ $spot[0] }} rail is free.
                            @else
                                {{ $total }} spots, beside the creators brands are already looking at.
                            @endif
                        </h1>
                        <p class="max-w-2xl text-base text-ink-700 sm:text-lg text-pretty">The cards in the rails on either side of this page are the only advertising on the site. One card is yours for {{ $days }} days and it runs everywhere a visitor on a wide screen goes: the home page, the directory, the leaderboard and every creator profile.</p>

                        @if($forSale)
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

                    {{-- The whole checkout: which spot, what it costs, where to send the link. --}}
                    <div class="card p-5 sm:p-6">
                        @if(! $forSale)
                            <p class="font-semibold text-ink-950">Not for sale right now</p>
                            <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink-600">We have taken the spots off the market. Mail <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a> and we will let you know when they are back.</p>
                        @else
                            @if($spotTaken)
                                <p class="mb-4 rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 text-[13px] text-ink-600">That one just went. {{ $open > 0 ? 'You take the next free spot instead.' : 'You take the first spot that comes free.' }}</p>
                            @endif
                            @if(session('error'))
                                <p class="mb-4 rounded-xl border border-danger-100 bg-white px-3 py-2 text-[13px] text-danger-700">{{ session('error') }}</p>
                            @endif

                            <p class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $full ? 'The next spot that comes free' : 'Your spot' }}</p>
                            <p class="mt-1 font-semibold text-ink-950">
                                @if($spot)
                                    {{ ucfirst($spot[0]) }} rail, {{ $ordinal($spot[1]) }} card
                                @elseif($full)
                                    {{ $queue > 0 ? 'Behind '.$queue.' other '.($queue === 1 ? 'buyer' : 'buyers') : 'First in line' }}
                                @else
                                    Whichever of the {{ $open }} free spots you like
                                @endif
                            </p>
                            <p class="mt-4 flex items-baseline gap-2">
                                <span class="display text-3xl tnum">{{ $due }}</span>
                                <span class="text-[13px] text-ink-500">for {{ $days }} days</span>
                            </p>

                            <form method="POST" action="{{ route('sponsor.checkout') }}" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="spot" value="{{ $spot ? $spot[0].$spot[1] : '' }}">
                                <div>
                                    <label class="label" for="email">Your email</label>
                                    <input id="email" name="email" type="email" required class="input" placeholder="you@company.com" value="{{ old('email') }}">
                                    <p class="mt-1.5 text-xs text-ink-400">Where the receipt and the link to your card go.</p>
                                    @error('email')<p class="mt-1 text-xs text-danger-700">{{ $message }}</p>@enderror
                                </div>
                                <button type="submit" class="btn-primary w-full">{{ $full ? 'Take the next spot' : 'Continue to payment' }}</button>
                            </form>
                            <p class="mt-3 text-[11.5px] leading-snug text-ink-500">Paying is what gets you the spot. If this one goes while you are at the checkout, you take the next one that comes free. You fill in the card afterwards and it goes up once you do.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot:hero>

    {{-- The whole deal in three steps. Everything a buyer needs to know is in here. --}}
    <section id="how" class="scroll-mt-24 mb-4">
        <h2 class="display text-2xl sm:text-[28px]">How it works</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Pick an open spot', 'Click any open slot on the site and you land here with that one selected. If everything is taken you are not stuck: you buy anyway and you take the first spot that comes free, in the order people paid.'],
                ['Pay for the month', ($price ?: 'The price').' covers '.$days.' days. One payment, no percentages, and nothing renews behind your back. Paying is what claims the spot, so nothing is reserved and nothing expires on you.'],
                ['Add your product or website', 'Straight after paying you get your own page. Fill in a name, your website and one line about what you make; we read the logo off your site for you. The card goes up as soon as it is complete, and that is when the '.$days.' days start.'],
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
