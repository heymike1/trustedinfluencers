{{-- A full-width mint band for pages rendered inside the layout slot (Livewire full-page components). --}}
@props(['wide' => false])
<div {{ $attributes->class(['band relative left-1/2 w-screen -translate-x-1/2 -mt-8 mb-6']) }}>
    <div class="mx-auto {{ $wide ? 'max-w-7xl' : 'max-w-6xl' }} px-4 sm:px-6 pt-6 pb-6 sm:pt-8">
        {{ $slot }}
    </div>
</div>
