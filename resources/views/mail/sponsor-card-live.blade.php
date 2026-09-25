<x-mail::message>
# Your card is up

**{{ $booking->name }}** is now in the {{ $booking->side }} rail, beside every page on {{ config('app.name') }}.

It runs until **{{ $booking->ends_at?->format('j F Y') }}**. Nothing renews by itself: when that day comes the card comes down and the spot goes back on the page.

<x-mail::button :url="$booking->cardUrl()">
Your card
</x-mail::button>

Change the text, the link or the logo whenever you like from that page. It also shows how many people clicked through.

{{ config('app.name') }}
</x-mail::message>
