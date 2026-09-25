<x-mail::message>
# Your card comes down on {{ $booking->ends_at?->format('j F') }}

**{{ $booking->name }}** has been in the {{ $booking->side }} rail since {{ $booking->starts_at?->format('j F') }} and its {{ \App\Support\Sponsorship::days() }} days are nearly up.

Nothing renews by itself, so there is nothing to cancel. If you want to keep the spot, reply to this email and we will sort it before someone else takes it.

<x-mail::button :url="$booking->cardUrl()">
Your card
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
