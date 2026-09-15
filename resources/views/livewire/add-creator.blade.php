<div class="mx-auto max-w-xl">
    <h1 class="text-2xl font-semibold tracking-tight text-ink-950">Add a creator</h1>
    <p class="mt-1 text-sm text-ink-500">Anyone can add a creator. The profile goes live straight away and only shows public info until the creator claims it.</p>

    <form wire:submit="submit" class="card mt-6 p-5 space-y-5">
        <div>
            <label class="label" for="name">Creator name</label>
            <input id="name" type="text" wire:model="name" class="input" placeholder="John Smith" autocomplete="off">
            <x-field-error for="name" />
        </div>

        <div>
            <p class="label">Platform</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach($platforms as $p)
                    <label class="flex items-center justify-center gap-2 rounded-md border px-3 py-2 text-sm cursor-pointer {{ $platform === $p->value ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 text-ink-700 hover:bg-ink-50' }}">
                        <input type="radio" wire:model.live="platform" value="{{ $p->value }}" class="sr-only">
                        <x-platform-icon :platform="$p" /> {{ $p->label() }}
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
                <div class="mt-2 rounded-md border border-ink-200 bg-ink-50 p-3 text-sm">
                    <p class="text-ink-700">This account is already listed.</p>
                    <a href="{{ route('creators.show', $existing) }}" class="mt-1 inline-flex items-center gap-2 font-medium text-ink-950 underline underline-offset-2">
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
