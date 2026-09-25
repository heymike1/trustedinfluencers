{{-- Shared shell for the error pages: the site band with a big number and a way back. --}}
@props(['code', 'title', 'body'])
<x-layouts.app :title="$title" :description="$body" :noindex="true">
    <x-slot:hero>
        <div class="band">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 pt-12 pb-14 sm:pt-16 sm:pb-20 grid gap-10 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-center">
                <div class="flex flex-col items-start gap-5">
                    <span class="eyebrow tnum">Error {{ $code }}</span>
                    <h1 class="display text-3xl leading-[1.1] sm:text-4xl lg:text-[40px] max-w-lg">{{ $title }}</h1>
                    <p class="max-w-md text-base text-ink-700 text-pretty">{{ $body }}</p>
                    <div class="flex flex-wrap items-center gap-3">
                        {{ $slot }}
                    </div>
                </div>
                @isset($art)
                    <div class="hidden lg:block">{{ $art }}</div>
                @endisset
            </div>
        </div>
    </x-slot:hero>

    <div class="grid gap-3.5 sm:grid-cols-3">
        @foreach([
            ['Browse creators', 'Every listed creator, filterable by platform and category.', route('creators.index'), '<circle cx="9" cy="9" r="6"/><path d="M13.5 13.5L17 17"/>'],
            ['Add a creator', 'Missing someone? A name and a handle is enough.', route('creators.create'), '<path d="M10 4v12M4 10h12"/>'],
            ['How verifying works', 'What a verified profile shows, and where the numbers come from.', route('about'), '<circle cx="10" cy="10" r="7"/><path d="M10 9.5V14M10 6.5h.01"/>'],
        ] as [$label, $line, $href, $icon])
            <a href="{{ $href }}" class="card flex items-start gap-3 p-4 hover:border-brand-200 transition-colors">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-[10px] border border-ink-200 bg-ink-50 text-ink-900"><svg class="size-[18px]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg></span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-ink-950">{{ $label }}</span>
                    <span class="block text-[13px] text-ink-500">{{ $line }}</span>
                </span>
            </a>
        @endforeach
    </div>
</x-layouts.app>
