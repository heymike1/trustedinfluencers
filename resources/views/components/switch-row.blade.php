{{-- A labelled on/off switch bound to a Livewire boolean. --}}
@props(['model', 'title', 'description' => null, 'accent' => false])
<label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border px-4 py-3.5 {{ $accent ? 'border-band-edge bg-verified-50' : 'border-ink-200 bg-ink-50' }}">
    <span>
        <span class="block text-sm font-semibold text-ink-950">{{ $title }}</span>
        @if($description)<span class="mt-0.5 block text-[13px] text-ink-500">{{ $description }}</span>@endif
    </span>
    <span class="relative shrink-0">
        <input type="checkbox" wire:model="{{ $model }}" class="peer sr-only">
        <span class="block h-[22px] w-10 rounded-full bg-ink-200 transition-colors peer-checked:bg-brand-700 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 peer-focus-visible:ring-offset-2"></span>
        <span class="absolute left-[3px] top-[3px] size-4 rounded-full bg-white transition-transform peer-checked:translate-x-[18px]"></span>
    </span>
</label>
