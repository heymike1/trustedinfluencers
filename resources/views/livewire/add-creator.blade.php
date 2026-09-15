<div>
    <x-page-band>
        <div class="mx-auto max-w-xl">
            <span class="eyebrow">Free, takes a minute</span>
            <h1 class="display mt-4 text-3xl sm:text-[40px] leading-[1.05]">Add a creator</h1>
            <p class="mt-3 text-[15px] text-ink-700 text-pretty">Anyone can add a creator. The profile goes live straight away and only shows public info until the creator claims it by signing in with the account itself.</p>
        </div>
    </x-page-band>

    <form wire:submit="submit" class="card mx-auto max-w-xl p-6 space-y-5">
        <div>
            <label class="label" for="name">Creator name</label>
            <input id="name" type="text" wire:model="name" class="input" placeholder="John Smith" autocomplete="off">
            <x-field-error for="name" />
        </div>

        <div>
            <p class="label">Platform</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach($platforms as $p)
                    <label class="flex items-center justify-center gap-2 rounded-full border px-3 py-2.5 text-sm font-medium cursor-pointer {{ $platform === $p->value ? 'border-brand-700 bg-brand-700 text-white' : 'border-ink-200 bg-white text-ink-700 hover:bg-ink-50' }}">
                        <input type="radio" wire:model.live="platform" value="{{ $p->value }}" class="sr-only">
                        <x-platform-icon :platform="$p" class="size-4" :colored="$platform !== $p->value" /> {{ $p->label() }}
                    </label>
                @endforeach
            </div>
            <x-field-error for="platform" />
        </div>

        <div>
            <label class="label" for="handle">Handle or profile URL</label>
            <input id="handle" type="text" wire:model.live.debounce.300ms="handle" class="input" placeholder="@johnsmith or youtube.com/@johnsmith" autocomplete="off">
            @if($preview)
                <p class="mt-1 text-xs text-ink-500">Will be listed as <span class="font-medium text-ink-900">{{ \App\Enums\Platform::from($platform)->label() }} {{ "@".$preview }}</span></p>
            @elseif(trim($handle) !== '')
                <p class="mt-1 text-xs text-warn-700">That doesn’t look like a {{ \App\Enums\Platform::from($platform)->label() }} handle or profile link.</p>
            @endif
            <x-field-error for="handle" />
            @if($existing)
                <div class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-band-edge bg-verified-50 px-4 py-3 text-sm">
                    <p class="text-ink-700">This account is already listed.</p>
                    <a href="{{ route('creators.show', $existing) }}" class="inline-flex items-center gap-2 font-semibold text-brand-700">
                        <x-avatar :creator="$existing" size="xs" /> {{ $existing->name }} →
                    </a>
                </div>
            @endif
        </div>

        <div>
            <label class="label" for="category">Category <span class="normal-case font-normal text-ink-400">(optional)</span></label>
            <select id="category" wire:model="category" class="input">
                <option value="">Choose a category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <x-field-error for="category" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <p class="text-xs text-ink-400">We tidy up handles and links so the same account can’t be added twice.</p>
            <button type="submit" class="btn-primary" wire:loading.attr="disabled">Add creator</button>
        </div>
    </form>
</div>
