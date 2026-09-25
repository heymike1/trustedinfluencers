<x-slot:heading>Content</x-slot:heading>
<x-slot:subheading>{{ number_format($contents->total()) }} imported items{{ $accountModel ? ' · '.$accountModel->handleWithAt().' ('.$accountModel->creator->name.')' : '' }}</x-slot:subheading>
<div>
    <x-notice :notice="$notice" />

    <div class="flex flex-wrap items-center gap-2 mb-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Title" class="input max-w-xs">
        <select wire:model.live="type" class="input w-auto">
            <option value="">Every type</option>
            @foreach($types as $t)<option value="{{ $t->value }}">{{ ucfirst($t->label()) }}</option>@endforeach
        </select>
        @if($accountModel)
            <button type="button" wire:click="$set('account', null)" class="chip">{{ $accountModel->handleWithAt() }} ✕</button>
        @endif
        <p class="ml-auto text-xs text-ink-500">Rows are replaced on every sync; editing them by hand would be overwritten.</p>
    </div>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead><tr><th class="pl-4">Item</th><th>Account</th><th>Type</th><th>Published</th><th class="text-right">Views</th><th class="text-right">Likes</th><th class="pr-4"></th></tr></thead>
            <tbody>
                @forelse($contents as $content)
                    <tr wire:key="c-{{ $content->id }}">
                        <td class="pl-4">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="h-8 w-12 shrink-0 overflow-hidden rounded bg-ink-100">@if($content->thumbnail_url)<img src="{{ $content->thumbnail_url }}" alt="" class="size-full object-cover" loading="lazy" onerror="this.remove()">@endif</span>
                                <span class="min-w-0">
                                    <span class="block max-w-[320px] truncate font-medium text-ink-950">{{ $content->title ?? '—' }}</span>
                                    <a href="{{ $content->url }}" target="_blank" rel="noopener nofollow" class="text-xs text-ink-500 hover:underline">{{ $content->provider_content_id }}</a>
                                </span>
                            </div>
                        </td>
                        <td class="text-xs"><a href="{{ route('admin.creators.show', $content->socialAccount->creator) }}" class="text-brand-700 hover:underline">{{ $content->socialAccount->handleWithAt() }}</a></td>
                        <td class="text-xs">{{ ucfirst($content->content_type->singular()) }}</td>
                        <td class="text-xs text-ink-500 whitespace-nowrap">{{ $content->published_at?->format('j M Y') ?? '—' }}</td>
                        <td class="text-right tnum">{{ \App\Support\Format::compact($content->metric('views')) }}</td>
                        <td class="text-right tnum">{{ \App\Support\Format::compact($content->metric('likes')) }}</td>
                        <td class="pr-4 text-right whitespace-nowrap">
                            <button type="button" wire:click="inspect({{ $content->id }})" class="btn-secondary btn-sm">{{ $inspecting === $content->id ? 'Hide' : 'Raw' }}</button>
                            <button type="button" wire:click="destroy({{ $content->id }})" wire:confirm="Delete this row?" class="btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                    @if($inspecting === $content->id)
                        <tr wire:key="raw-{{ $content->id }}">
                            <td colspan="7" class="bg-ink-50 px-4 py-3">
                                <pre class="max-h-72 overflow-auto rounded-lg border border-ink-200 bg-white p-3 text-xs">{{ json_encode(['metrics' => $content->metrics, 'insights' => $content->insights], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="7" class="py-10 text-center text-sm text-ink-500">Nothing imported yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $contents->links() }}</div>
</div>
