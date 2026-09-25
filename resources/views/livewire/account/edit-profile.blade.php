<div>
    @if(! $creator)
        <x-page-band>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="display text-2xl sm:text-[26px] tracking-[-0.025em]">My profile</h1>
                    <p class="mt-0.5 text-[13.5px] text-ink-500">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('account.login') }}" class="btn-secondary border-band-edge">
                    <svg class="size-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="7" r="3"/><path d="M4 17c0-3 2.7-5 6-5s6 2 6 5"/></svg>
                    Manage my account
                </a>
            </div>
        </x-page-band>
        <x-no-creator />
    @else
        <x-account-nav :creator="$creator" />

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
            <form wire:submit="save" class="space-y-4">
                <section class="card p-6 space-y-5">
                    <div>
                        <h2 class="text-[15px] font-semibold text-ink-950">Basics</h2>
                        <p class="text-[13px] text-ink-500">What visitors see at the top of your profile.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <x-avatar :creator="$creator" size="lg" />
                        <div class="min-w-0 flex-1">
                            <label class="label" for="avatar_url">Profile photo URL</label>
                            <input id="avatar_url" type="url" wire:model="avatar_url" class="input" placeholder="https://…">
                            <p class="mt-1.5 text-xs text-ink-400">Leave it empty and we use the photo from your connected account.</p>
                            <x-field-error for="avatar_url" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="name">Display name</label>
                            <input id="name" type="text" wire:model="name" class="input">
                            <x-field-error for="name" />
                        </div>
                        <div>
                            <label class="label" for="category">Category</label>
                            <select id="category" wire:model="category" class="input">
                                <option value="">None</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <x-field-error for="category" />
                        </div>
                    </div>
                    <div x-data="{ n: 0 }">
                        <label class="label" for="bio">Bio</label>
                        <textarea id="bio" rows="3" wire:model="bio" x-on:input="n = $event.target.value.length" x-init="n = $el.value.length" class="input" maxlength="500"></textarea>
                        <p class="mt-1.5 flex justify-between text-xs text-ink-400"><span>Two or three sentences is plenty.</span><span class="tnum"><span x-text="n"></span> / 500</span></p>
                        <x-field-error for="bio" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="location">Location</label>
                            <input id="location" type="text" wire:model="location" class="input">
                            <x-field-error for="location" />
                        </div>
                        <div>
                            <label class="label" for="website">Website</label>
                            <input id="website" type="url" wire:model="website" class="input" placeholder="https://…">
                            <x-field-error for="website" />
                        </div>
                    </div>
                </section>

                <section class="card p-6 space-y-5">
                    <div>
                        <h2 class="text-[15px] font-semibold text-ink-950">Contact</h2>
                        <p class="text-[13px] text-ink-500">How brands reach you. Your address is never shown publicly.</p>
                    </div>
                    <x-switch-row model="contact_enabled" title="Accept contact requests through my profile" description="Visitors get a short form; the message lands in your inbox here and by email." />
                    <div class="sm:w-1/2">
                        <label class="label" for="contact_email">Contact email</label>
                        <input id="contact_email" type="email" wire:model="contact_email" class="input" placeholder="Defaults to your login email">
                        <p class="mt-1.5 text-xs text-ink-400">Nobody sees the address itself.</p>
                        <x-field-error for="contact_email" />
                    </div>
                </section>

                <section class="card p-6 space-y-5">
                    <div>
                        <h2 class="text-[15px] font-semibold text-ink-950">Visibility</h2>
                        <p class="text-[13px] text-ink-500">Who can find your profile.</p>
                    </div>
                    <x-switch-row model="is_listed" title="Listed in the directory" description="Turn this off and your profile disappears from the directory, leaderboard and search engines. Only you can still open it. Nothing is deleted." :accent="true" />
                </section>

                <div class="sticky bottom-4 z-10 flex items-center justify-between gap-3 rounded-2xl border border-ink-200 bg-white px-5 py-3.5 shadow-[0_8px_24px_rgba(20,23,21,0.08)]">
                    <span class="text-[13px] text-ink-500"><span wire:dirty>Unsaved changes</span><span wire:dirty.remove>Everything is saved</span></span>
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">Save changes</button>
                </div>

            </form>

            <aside class="space-y-4">
                @php($done = collect($checklist)->where('done', true)->count())
                <div class="card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[15px] font-semibold text-ink-950">Profile checklist</h2>
                        <span class="text-[13px] text-ink-500 tnum">{{ $done }} of {{ count($checklist) }}</span>
                    </div>
                    <div class="mt-3 h-1.5 rounded-full bg-ink-100"><div class="h-1.5 rounded-full bg-brand-600" style="width: {{ count($checklist) ? round($done / count($checklist) * 100) : 0 }}%"></div></div>
                    <ul class="mt-4 space-y-2.5 text-[13.5px]">
                        @foreach($checklist as $item)
                            <li class="flex items-center gap-2.5 {{ $item['done'] ? 'text-ink-500' : '' }}">
                                @if($item['done'])
                                    <span class="inline-flex size-[18px] shrink-0 items-center justify-center rounded-full bg-verified-50 text-brand-700"><svg class="size-2.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg></span>
                                    <span class="line-through">{{ $item['label'] }}</span>
                                @else
                                    <span class="inline-block size-[18px] shrink-0 rounded-full border-[1.5px] border-ink-200"></span>
                                    @if($item['href'])<a href="{{ $item['href'] }}" class="font-semibold text-brand-700">{{ $item['label'] }}</a>@else<span>{{ $item['label'] }}</span>@endif
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card p-5 text-[13.5px] text-ink-500 space-y-2">
                    <p class="font-semibold text-ink-950">What you can’t edit</p>
                    <p>Your verified numbers, follower count and content stats come straight from the platform, so there’s nothing to edit here. To refresh or reconnect, go to <a href="{{ route('account.connections') }}" class="font-semibold text-brand-700">Connected accounts</a>.</p>
                </div>
            </aside>
        </div>
    @endif
</div>
