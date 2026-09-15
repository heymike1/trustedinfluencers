<x-mail::message>
# New contact request

**{{ $contactRequest->name }}**@if($contactRequest->company) ({{ $contactRequest->company }})@endif sent you a message through {{ config('app.name') }}.

**Subject:** {{ $contactRequest->subject }}

{{ $contactRequest->message }}

Just reply to this email to get back to {{ $contactRequest->name }} ({{ $contactRequest->email }}).

<x-mail::button :url="route('account.requests')">
View requests
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
