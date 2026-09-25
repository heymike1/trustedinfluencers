<x-slot:heading>Overview</x-slot:heading>
<x-slot:subheading>{{ now()->format('l j F, H:i') }}</x-slot:subheading>
<div class="space-y-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach($stats as [$label, $value, $hint, $href])
            <a href="{{ $href }}" class="card p-4 hover:border-brand-200 transition-colors">
                <p class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $label }}</p>
                <p class="display mt-1 text-2xl tracking-[-0.02em] tnum">{{ number_format($value) }}</p>
                <p class="text-xs text-ink-500 tnum">{{ $hint }}</p>
            </a>
        @endforeach
    </div>

    @if($attentionCount || $pendingClaims || $failedJobs || $staleCount)
        <div class="card border-warn-100 p-5">
            <h2 class="text-[15px] font-semibold text-ink-950">Needs a look</h2>
            <ul class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-[13.5px] text-ink-700 tnum">
                @if($attentionCount)<li><a href="{{ route('admin.accounts', ['status' => 'needs_reconnection']) }}" class="font-semibold text-brand-700">{{ $attentionCount }}</a> accounts failing or needing reconnection</li>@endif
                @if($pendingClaims)<li><a href="{{ route('admin.claims', ['status' => 'pending']) }}" class="font-semibold text-brand-700">{{ $pendingClaims }}</a> claims still pending</li>@endif
                @if($staleCount)<li><span class="font-semibold text-ink-950">{{ $staleCount }}</span> verified profiles with stale numbers</li>@endif
                @if($failedJobs)<li><span class="font-semibold text-ink-950">{{ $failedJobs }}</span> failed jobs in the queue</li>@endif
            </ul>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="card overflow-hidden">
            <h2 class="px-5 py-4 text-[15px] font-semibold text-ink-950">Accounts needing attention</h2>
            <table class="data-table">
                <tbody>
                    @forelse($needsAttention as $account)
                        <tr wire:key="att-{{ $account->id }}">
                            <td class="pl-5">
                                <a href="{{ route('admin.creators.show', $account->creator) }}" class="font-medium text-ink-950 hover:underline">{{ $account->creator->name }}</a>
                                <p class="text-xs text-ink-500">{{ $account->platform->label() }} {{ $account->handleWithAt() }}</p>
                            </td>
                            <td class="pr-5 text-right"><x-badge variant="warn">{{ $account->connection_status->label() }}</x-badge></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-sm text-ink-500">Everything is syncing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card overflow-hidden">
            <h2 class="px-5 py-4 text-[15px] font-semibold text-ink-950">Latest claims</h2>
            <table class="data-table">
                <tbody>
                    @forelse($recentClaims as $claim)
                        <tr wire:key="cl-{{ $claim->id }}">
                            <td class="pl-5">
                                <p class="font-medium text-ink-950">{{ $claim->creator?->name ?? 'deleted' }}</p>
                                <p class="text-xs text-ink-500">{{ $claim->user?->email ?? '—' }}</p>
                            </td>
                            <td class="pr-5 text-right"><x-badge :variant="$claim->status === \App\Enums\ClaimStatus::Verified ? 'verified' : ($claim->status === \App\Enums\ClaimStatus::Failed ? 'danger' : 'neutral')">{{ ucfirst($claim->status->value) }}</x-badge></td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-sm text-ink-500">No claims yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card overflow-hidden">
            <h2 class="px-5 py-4 text-[15px] font-semibold text-ink-950">Newest profiles</h2>
            <table class="data-table">
                <tbody>
                    @forelse($recentCreators as $creator)
                        <tr wire:key="nc-{{ $creator->id }}">
                            <td class="pl-5">
                                <a href="{{ route('admin.creators.show', $creator) }}" class="font-medium text-ink-950 hover:underline">{{ $creator->name }}</a>
                                <p class="text-xs text-ink-500">{{ $creator->category?->name ?? 'No category' }} · {{ $creator->created_at->diffForHumans() }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-8 text-center text-sm text-ink-500">Nothing listed yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5 text-[13.5px] text-ink-500">
        <h2 class="text-[15px] font-semibold text-ink-950">Settings in effect</h2>
        <dl class="mt-2 grid gap-x-8 gap-y-1 sm:grid-cols-2 lg:grid-cols-4 tnum">
            <div class="flex justify-between gap-3"><dt>Connector driver</dt><dd class="font-medium text-ink-950">{{ config('social.driver') }}</dd></div>
            <div class="flex justify-between gap-3"><dt>Platforms on</dt><dd class="font-medium text-ink-950">{{ \App\Enums\Platform::enabledLabels(' and ') }}</dd></div>
            <div class="flex justify-between gap-3"><dt>Refresh every</dt><dd class="font-medium text-ink-950">{{ config('social.sync.refresh_every_hours') }}h</dd></div>
            <div class="flex justify-between gap-3"><dt>Hidden / unlisted</dt><dd class="font-medium text-ink-950">{{ $hiddenCount }} / {{ $unlistedCount }}</dd></div>
        </dl>
    </div>
</div>
