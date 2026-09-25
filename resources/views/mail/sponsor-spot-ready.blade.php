<x-mail::message>
# A spot came free, and it is yours

You were next in line. Your card takes the **{{ $booking->position }}{{ [1 => 'st', 2 => 'nd', 3 => 'rd'][$booking->position] ?? 'th' }} place in the {{ $booking->side }} rail**.

@if($booking->hasCard())
It is going up now and runs for {{ \App\Support\Sponsorship::days() }} days.
@else
Fill in what it should say and it goes up straight away. The {{ \App\Support\Sponsorship::days() }} days start then.
@endif

<x-mail::button :url="$booking->cardUrl()">
{{ $booking->hasCard() ? 'View your card' : 'Fill in your card' }}
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
