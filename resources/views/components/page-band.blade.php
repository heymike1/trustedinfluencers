{{-- The header block of a page rendered inside the layout slot (Livewire full-page components).
     It stays inside the content column: a full-width, positioned block would paint over the
     sponsor rails beside the page. --}}
@props(['wide' => false])
<div {{ $attributes->class(['-mt-2 mb-6']) }}>
    {{ $slot }}
</div>
