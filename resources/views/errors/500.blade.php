<x-errors.layout
    code="500"
    title="Something broke on our side."
    body="Not your fault, and nothing you did is lost. We’ve been notified and we’re looking at it. Try again in a minute."
>
    <a href="{{ url()->current() }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Try again <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 10a6 6 0 1 1-1.8-4.3M16 3v3.5h-3.5"/></svg></a>
    <a href="{{ url('/') }}" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">Back to home</a>

    <x-slot:art>
        {{-- A profile card mid-refresh: the numbers are fine, the page just couldn't draw them. --}}
        <div class="card p-5" aria-hidden="true">
            <div class="flex items-center gap-3">
                <span class="size-10 shrink-0 rounded-full bg-ink-100"></span>
                <span class="flex-1 space-y-1.5">
                    <span class="block h-2.5 w-32 rounded-full bg-ink-100"></span>
                    <span class="block h-2 w-24 rounded-full bg-ink-100/70"></span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-verified-50 px-2 py-1 text-[11px] font-semibold text-brand-700"><span class="size-1.5 animate-pulse rounded-full bg-brand-600"></span> Retrying</span>
            </div>
            <div class="mt-5 grid grid-cols-3 gap-2.5">
                @foreach(['Median views', 'Reach', 'Engagement'] as $label)
                    <div class="rounded-xl border border-ink-100 bg-ink-50 px-3 py-3">
                        <span class="block text-[11px] text-ink-500">{{ $label }}</span>
                        <span class="mt-1.5 block h-3 w-10 rounded-full bg-ink-200"></span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-ink-100">
                <div class="h-1.5 w-1/3 rounded-full bg-brand-600"></div>
            </div>
        </div>
    </x-slot:art>
</x-errors.layout>
