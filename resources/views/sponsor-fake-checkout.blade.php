{{-- The stand-in checkout. Only reachable while the fake driver is on. --}}
<x-layouts.app title="Checkout" :noindex="true">
    <div class="mx-auto max-w-md card p-6">
        <p class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 bg-ink-50 px-2.5 py-1 text-xs font-semibold text-ink-700">Test checkout</p>
        <h1 class="display mt-3 text-2xl">{{ $price }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            Sponsor spot for {{ \App\Support\Sponsorship::days() }} days
            @if($booking->position)
                · {{ $booking->side }} rail, card {{ $booking->position }}
            @else
                · the first spot that comes free
            @endif
        </p>
        <p class="mt-4 text-[13px] text-ink-600">No payment provider is connected yet. This button does what a completed payment would do.</p>
        <form method="POST" action="{{ route('sponsor.checkout.fake.pay', $booking) }}" class="mt-4">
            @csrf
            <button type="submit" class="btn-primary w-full">Pay {{ $price }}</button>
        </form>
        <a href="{{ route('sponsor') }}" class="mt-3 block text-center text-[13px] text-ink-500 hover:text-ink-900">Cancel</a>
    </div>
</x-layouts.app>
