<x-layouts.app title="One moment" :noindex="true">
    <div class="mx-auto max-w-lg card p-6 text-center">
        <p class="font-semibold text-ink-950">Your payment is going through.</p>
        <p class="mt-1.5 text-sm text-ink-500">This usually takes a few seconds. We email the link to your card the moment it lands, so you can close this page safely.</p>
        <div class="mt-4 flex justify-center gap-2">
            <a href="{{ route('sponsor.pending', $booking) }}" class="btn-primary btn-sm">Check again</a>
            <a href="{{ route('home') }}" class="btn-secondary btn-sm">Back to the site</a>
        </div>
    </div>
    <meta http-equiv="refresh" content="5">
</x-layouts.app>
