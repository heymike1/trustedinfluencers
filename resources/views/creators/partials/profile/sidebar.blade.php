@php use App\Support\Format; $account = $i->account; @endphp
<div class="flex flex-col gap-3">
    <div class="card p-4 text-[13px] text-ink-700 space-y-2.5">
        <p class="text-sm font-semibold text-ink-950">How these numbers were verified</p>
        <p class="flex gap-2"><svg class="mt-0.5 size-4 shrink-0 text-brand-700" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg><span>{{ \Illuminate\Support\Str::before($creator->name, ' ') }} signed in to {{ $account->platform->label() }} as <span class="font-mono text-xs text-ink-900">{{ $account->handleWithAt() }}</span>, the same account this profile lists.</span></p>
        <p class="flex gap-2"><svg class="mt-0.5 size-4 shrink-0 text-brand-700" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg><span>Every number here came straight from {{ $account->platform->label() }}. {{ \Illuminate\Support\Str::before($creator->name, ' ') }} can’t edit any of it.</span></p>
        <p class="flex gap-2"><svg class="mt-0.5 size-4 shrink-0 text-brand-700" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10.5l4 4 8-9"/></svg><span>Last synced {{ $account->last_synced_at->diffForHumans() }}. Refreshed daily.</span></p>
        <p class="border-t border-ink-100 pt-2.5 text-xs text-ink-500">
            This is not {{ $account->platform->label() }}’s own verification badge.
            @if($account->platform === \App\Enums\Platform::YouTube) YouTube doesn’t share impressions or thumbnail clicks with anyone, so they aren’t shown.
            @elseif($account->platform === \App\Enums\Platform::Instagram) Instagram no longer shares impressions, so views are used instead.
            @else X doesn’t share who the audience is, so there’s no age or country breakdown.
            @endif
        </p>
    </div>

    <div class="card p-4 text-[13px]">
        <p class="text-sm font-semibold text-ink-950 mb-2">{{ $account->platform === \App\Enums\Platform::YouTube ? 'Channel' : 'Account' }}</p>
        <dl class="space-y-1.5 tnum">
            <div class="flex justify-between"><dt class="text-ink-500">{{ ucfirst($account->platform->audienceNoun()) }}</dt><dd class="font-medium">{{ Format::compact($account->follower_count) }}</dd></div>
            @foreach($totals as $label => $value)
                <div class="flex justify-between"><dt class="text-ink-500">{{ $label }}</dt><dd class="font-medium {{ str_starts_with((string) $value, '+') ? 'text-brand-700' : '' }}">{{ $value }}</dd></div>
            @endforeach
        </dl>
    </div>

    @if($creator->contact_enabled)
        <div class="card p-4 text-[13px]">
            <p class="text-sm font-semibold text-ink-950">Contact for brand deals</p>
            <p class="mt-1 text-ink-700">{{ \Illuminate\Support\Str::before($creator->name, ' ') }} takes contact requests. Replies come straight to your email.</p>
            <a href="#contact" class="btn-primary btn-sm mt-3 w-full">Send a request</a>
        </div>
    @endif
</div>
