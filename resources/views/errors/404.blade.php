<x-errors.layout
    code="404"
    title="This page isn’t here."
    body="The link may be old, or the profile was removed by its creator. Everything else still works."
>
    <a href="{{ url('/') }}" class="btn-primary !px-5 !py-2.5 !text-[15px]">Back to home <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10h11M11 5l5 5-5 5"/></svg></a>
    <a href="{{ route('creators.index') }}" class="btn-secondary !px-5 !py-2.5 !text-[15px] border-band-edge">Search the directory</a>

    <x-slot:art>
        {{-- An empty result row: the shape of the page you were looking for. --}}
        <div class="card overflow-hidden" aria-hidden="true">
            <div class="flex items-center justify-between border-b border-ink-100 bg-ink-50 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-ink-500">
                <span>Creator</span><span>Median views</span>
            </div>
            @foreach([1, 2] as $row)
                <div class="flex items-center gap-3 px-4 py-3.5 {{ $row === 2 ? 'border-t border-ink-100' : '' }}">
                    <span class="size-8 shrink-0 rounded-full bg-ink-100"></span>
                    <span class="flex-1 space-y-1.5">
                        <span class="block h-2.5 w-28 rounded-full bg-ink-100"></span>
                        <span class="block h-2 w-20 rounded-full bg-ink-100/70"></span>
                    </span>
                    <span class="h-2.5 w-10 rounded-full bg-ink-100"></span>
                </div>
            @endforeach
            <div class="flex items-center gap-3 border-t border-dashed border-band-edge bg-verified-50 px-4 py-3.5">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-full border border-dashed border-band-edge text-brand-700"><svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 10h8"/></svg></span>
                <span class="text-[13px] font-medium text-brand-700">Nothing at this address</span>
            </div>
        </div>
    </x-slot:art>
</x-errors.layout>
