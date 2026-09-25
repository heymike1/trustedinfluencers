<x-mail::message>
# Thanks, that is paid

@if($booking->isQueued())
Every spot was taken when you paid, so you are next in line for the first one that comes free. The moment it does, we put your card up and email you — you do not have to do anything.

@if(\App\Support\Sponsorship::nextFreeAt())
As it stands that is around **{{ \App\Support\Sponsorship::nextFreeAt()->format('j F Y') }}**.
@endif

You can already fill in what your card should say:
@else
Your spot is the **{{ $booking->position }}{{ [1 => 'st', 2 => 'nd', 3 => 'rd'][$booking->position] ?? 'th' }} card in the {{ $booking->side }} rail**. One thing left: tell us what goes in it.

Add your name, your website and one line about what you make. We pull your logo off the site for you. Your {{ \App\Support\Sponsorship::days() }} days start the moment the card goes up, not today, so take your time.
@endif

<x-mail::button :url="$booking->cardUrl()">
Fill in your card
</x-mail::button>

Keep this link. It is how you change the card later and see how many people clicked it.

{{ config('app.name') }}
</x-mail::message>
