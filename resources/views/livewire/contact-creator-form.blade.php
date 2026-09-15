<div class="card p-5">
    @if(! $creator->contact_enabled)
        <p class="text-sm text-ink-500">{{ $creator->name }} isn’t taking contact requests right now.</p>
    @elseif($sent)
        <div class="text-sm">
            <p class="font-medium text-ink-950">Request sent.</p>
            <p class="mt-1 text-ink-500">
                @if($creator->isClaimed())
                    {{ $creator->name }} has your message and can reply to you by email.
                @else
                    This profile hasn’t been claimed yet, so we’ve kept your message. {{ $creator->name }} will see it as soon as they claim it.
                @endif
            </p>
            <button type="button" wire:click="$set('sent', false)" class="mt-3 text-xs text-ink-700 underline underline-offset-2">Send another</button>
        </div>
    @else
        @if(! $creator->isClaimed())
            <p class="mb-4 rounded-md border border-ink-200 bg-ink-50 px-3 py-2 text-xs text-ink-600">Nobody has claimed this profile yet. We’ll keep your message and show it to the creator if they do.</p>
        @endif
        <form wire:submit="submit" class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="c-name">Your name</label>
                <input id="c-name" type="text" wire:model="name" class="input">
                <x-field-error for="name" />
            </div>
            <div>
                <label class="label" for="c-email">Email</label>
                <input id="c-email" type="email" wire:model="email" class="input">
                <x-field-error for="email" />
            </div>
            <div>
                <label class="label" for="c-company">Company <span class="normal-case font-normal text-ink-400">(optional)</span></label>
                <input id="c-company" type="text" wire:model="company" class="input">
                <x-field-error for="company" />
            </div>
            <div>
                <label class="label" for="c-subject">Subject</label>
                <input id="c-subject" type="text" wire:model="subject" class="input" placeholder="Sponsored video, Q4 campaign…">
                <x-field-error for="subject" />
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="c-message">Message</label>
                <textarea id="c-message" rows="5" wire:model="message" class="input" placeholder="What do you have in mind, when, and roughly what budget?"></textarea>
                <x-field-error for="message" />
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">Send request</button>
            </div>
        </form>
    @endif
</div>
