@php
    use App\Support\Sponsorship;

    $total = Sponsorship::total();
    $open = Sponsorship::open();
    $forSale = Sponsorship::isForSale();
    $full = $forSale && Sponsorship::isFull();
    $price = Sponsorship::price();
    // A full site charges for the next spot that frees up, which may be priced differently.
    $due = Sponsorship::money(Sponsorship::amountDueNow());
    $days = Sponsorship::days();
    $nextFree = Sponsorship::nextFreeAt();
    $queue = Sponsorship::queueLength();

    $ordinal = fn (int $n) => $n.([1 => 'st', 2 => 'nd', 3 => 'rd'][$n] ?? 'th');

    // Every spot on the page, as the picker needs it: what is in it, and when it comes free.
    $spots = Sponsorship::spots()->map(function (array $spot) use ($ordinal) {
        $booking = $spot['booking'];
        $live = $booking?->isLive() ? $booking : null;

        return $spot + [
            'label' => ucfirst($spot['side']).' rail, '.$ordinal($spot['position']).' card',
            'open' => $booking === null,
            'name' => $live?->name ?? ($booking ? 'Taken' : null),
            'tint' => $live?->tintClasses(),
            'logo' => $live?->logo_url,
            'free' => $live?->ends_at,
        ];
    });

    // The spot they clicked, if it is still there; otherwise the first free one.
    $asked = Sponsorship::parseSpot(request('spot'));
    $spotTaken = $asked && ! Sponsorship::isSpotOpen(request('spot'));
    $picked = $spotTaken || ! $asked ? Sponsorship::openSpots()->first() : request('spot');
    $labels = $spots->pluck('label', 'key');
@endphp
<x-layouts.app title="Sponsor" description="One card in the rails beside every page of {{ config('app.name') }}, for {{ $days }} days. {{ $total }} spots in total, booked one month at a time." :canonical="route('sponsor')" :rails="false">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-10 pb-8 sm:pt-14 sm:pb-10 flex flex-wrap items-end justify-between gap-x-10 gap-y-5">
                <div class="max-w-2xl">
                    <span class="eyebrow">Sponsor {{ config('app.name') }}</span>
                    <h1 class="display mt-4 text-3xl leading-[1.05] sm:text-4xl lg:text-[44px]">{{ $full ? 'Every spot is running.' : 'Pick the spot you want.' }}</h1>
                    <p class="mt-3 max-w-xl text-base text-ink-700 sm:text-[17px] text-pretty">{{ $total }} cards sit beside every page on the site: the home page, the directory, the leaderboard and every creator profile. Take one for {{ $days }} days.</p>
                </div>
                @if($forSale)
                    <p class="text-[13px] leading-relaxed text-ink-600 sm:text-right">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-band-edge bg-white px-2.5 py-1 font-semibold {{ $full ? 'text-ink-700' : 'text-brand-700' }}">
                            <span class="size-1.5 rounded-full {{ $full ? 'bg-ink-400' : 'bg-brand-600' }}"></span>
                            {{ $full ? 'All '.$total.' spots are taken' : $open.' of '.$total.' open' }}
                        </span>
                        <span class="mt-1.5 block">Booked per month, {{ $days }} days from the day your card goes up.<br class="hidden sm:inline"> No auto-renew.</span>
                    </p>
                @endif
            </div>
        </div>
    </x-slot:hero>

    <div data-spot-picker class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">

        {{-- The rails as they are, drawn to scale: the free ones are the buttons. --}}
        <section class="card p-4 sm:p-6">
            <div class="flex gap-4 sm:gap-6">
                @foreach(['left', 'right'] as $side)
                    <div class="flex min-w-0 flex-1 flex-col gap-2.5 md:max-w-[190px] {{ $side === 'right' ? 'md:order-3' : '' }}">
                        <p class="text-[11.5px] font-semibold uppercase tracking-wide text-ink-500">{{ ucfirst($side) }} rail</p>
                        @foreach($spots->where('side', $side) as $spot)
                            @if($spot['open'] && $forSale)
                                <button type="button" data-spot="{{ $spot['key'] }}" data-label="{{ $spot['label'] }}"
                                        class="spot block rounded-2xl border border-dashed border-ink-400 px-3 py-4 text-center transition-colors hover:bg-ink-50 {{ $spot['key'] === $picked ? 'is-picked' : '' }}">
                                    <span class="spot-icon mx-auto flex size-7 items-center justify-center rounded-lg border border-dashed border-ink-400 text-ink-400">
                                        <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 5v10M5 10h10"/></svg>
                                    </span>
                                    <span data-spot-name class="mt-2 block text-[13px] font-semibold text-brand-700">{{ $spot['key'] === $picked ? 'Your card' : 'Open' }}</span>
                                    <span class="mt-0.5 block text-[11.5px] text-ink-600 tnum">{{ $price }} / {{ $days }}d</span>
                                </button>
                            @else
                                <div class="rounded-2xl border px-3 py-4 text-center {{ $spot['tint'] ?? 'border-ink-200 bg-white/60' }}">
                                    @if($spot['logo'])
                                        <img src="{{ $spot['logo'] }}" alt="" class="mx-auto size-7 rounded-lg object-cover" loading="lazy" onerror="this.remove()">
                                    @else
                                        <span class="mx-auto flex size-7 items-center justify-center rounded-lg bg-white/70 text-[10px] font-bold text-ink-700">{{ $spot['name'] ? mb_strtoupper(mb_substr($spot['name'], 0, 2)) : '—' }}</span>
                                    @endif
                                    <span class="mt-2 block truncate text-[13px] font-semibold text-ink-950">{{ $spot['name'] ?? 'Not for sale' }}</span>
                                    <span class="mt-0.5 block text-[11.5px] text-ink-600 tnum">{{ $spot['free'] ? 'free '.$spot['free']->format('j M') : '' }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach

                {{-- The page the cards sit beside. Decoration, so it steps aside on a phone. --}}
                <div class="hidden flex-1 flex-col gap-3 self-stretch rounded-2xl border border-ink-100 bg-ink-50 p-5 md:order-2 md:flex" aria-hidden="true">
                    <div class="mx-auto h-2.5 w-3/5 rounded-full bg-ink-200"></div>
                    <div class="mx-auto mb-1 h-2.5 w-2/5 rounded-full bg-ink-100"></div>
                    <div class="flex flex-1 flex-col gap-3 rounded-xl border border-ink-200 bg-white p-4">
                        <p class="text-[11.5px] font-bold text-ink-950">Leaderboard</p>
                        @foreach([110, 86, 124, 96, 112, 78] as $w)
                            <div class="flex items-center gap-2.5">
                                <div class="size-5 shrink-0 rounded-full bg-ink-100"></div>
                                <div class="h-2 rounded-full bg-ink-100" style="width: {{ $w }}px"></div>
                                <div class="ml-auto h-2 w-10 rounded-full bg-ink-200"></div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-center text-[11.5px] text-ink-500">The page your card sits beside</p>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="card p-5 sm:p-6">
                @if(! $forSale)
                    <p class="font-semibold text-ink-950">Not for sale right now</p>
                    <p class="mt-1.5 text-[13.5px] leading-relaxed text-ink-600">We have taken the spots off the market. Mail <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a> and we will let you know when they are back.</p>
                @else
                    @if($spotTaken)
                        <p class="mb-4 rounded-xl border border-ink-200 bg-ink-50 px-3 py-2 text-[13px] text-ink-600">That one just went. {{ $open > 0 ? 'Pick another.' : 'You take the first spot that comes free.' }}</p>
                    @endif
                    @if(session('error'))
                        <p class="mb-4 rounded-xl border border-danger-100 bg-white px-3 py-2 text-[13px] text-danger-700">{{ session('error') }}</p>
                    @endif

                    <p class="text-[11.5px] font-semibold uppercase tracking-wide text-ink-500">{{ $full ? 'The next spot that comes free' : 'Your spot' }}</p>
                    <p class="mt-1.5 font-semibold text-ink-950">
                        @if($full)
                            {{ $nextFree ? $nextFree->format('j F Y') : 'As soon as one ends' }}{{ $queue > 0 ? ' · behind '.$queue.' other '.($queue === 1 ? 'buyer' : 'buyers') : '' }}
                        @else
                            <span data-spot-label>{{ $labels[$picked] ?? '' }}</span>
                        @endif
                    </p>

                    <p class="mt-4 flex items-baseline gap-2">
                        <span class="display text-3xl tnum">{{ $due }}</span>
                        <span class="text-[13.5px] text-ink-500">for {{ $days }} days</span>
                    </p>

                    <form method="POST" action="{{ route('sponsor.checkout') }}" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="spot" data-spot-input value="{{ $full ? '' : $picked }}">
                        <div>
                            <label class="label" for="email">Your email</label>
                            <input id="email" name="email" type="email" required class="input" placeholder="you@company.com" value="{{ old('email') }}">
                            <p class="mt-1.5 text-xs text-ink-400">Where the receipt and the link to your card go.</p>
                            @error('email')<p class="mt-1 text-xs text-danger-700">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-primary w-full">{{ $full ? 'Take the next spot' : 'Continue to payment' }}</button>
                    </form>
                    <p class="mt-3 text-[11.5px] leading-snug text-ink-500">Paying claims the spot. You add your logo and one line right after, and the card goes up once you do.</p>
                @endif
            </div>

            <div class="card p-5 sm:p-6 space-y-3">
                @foreach([
                    'Pay for the spot you picked. If it goes while you are at the checkout, you take the next one that comes free.',
                    'Add your name, your website and one line. Your logo comes along with your domain.',
                    'It runs '.$days.' days from the day it appears, then it comes down by itself.',
                ] as $i => $step)
                    <div class="flex gap-2.5">
                        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-[11px] font-bold text-brand-700 tnum">{{ $i + 1 }}</span>
                        <span class="text-[13px] leading-relaxed text-ink-600">{{ $step }}</span>
                    </div>
                @endforeach
                <p class="border-t border-ink-100 pt-3 text-[13px] text-ink-500">Questions first: <a href="mailto:{{ Sponsorship::contact() }}" class="font-semibold text-brand-700">{{ Sponsorship::contact() }}</a>.</p>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            var picker = document.querySelector('[data-spot-picker]');
            if (! picker) return;

            var input = picker.querySelector('[data-spot-input]');
            var label = picker.querySelector('[data-spot-label]');
            var spots = picker.querySelectorAll('.spot');

            spots.forEach(function (spot) {
                spot.addEventListener('click', function () {
                    spots.forEach(function (other) {
                        other.classList.toggle('is-picked', other === spot);
                        other.querySelector('[data-spot-name]').textContent = other === spot ? 'Your card' : 'Open';
                    });

                    if (input) input.value = spot.dataset.spot;
                    if (label) label.textContent = spot.dataset.label;
                });
            });
        })();
    </script>
</x-layouts.app>
