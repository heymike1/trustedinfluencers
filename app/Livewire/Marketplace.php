<?php

namespace App\Livewire;

use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorSocialAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Marketplace extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $platform = '';

    #[Url(except: '')]
    public string $category = '';

    /** '' | claimed | unclaimed */
    #[Url(except: '')]
    public string $claimed = '';

    #[Url(except: false)]
    public bool $verified = false;

    #[Url(except: '')]
    public string $followersMin = '';

    #[Url(except: '')]
    public string $followersMax = '';

    #[Url(except: '')]
    public string $medianViewsMin = '';

    #[Url(except: '')]
    public string $averageViewsMin = '';

    #[Url(except: '')]
    public string $engagementMin = '';

    #[Url(except: 'followers')]
    public string $sort = 'followers';

    /** table | cards */
    #[Url(except: 'table')]
    public string $view = 'table';

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'claimed', 'verified', 'followersMin', 'followersMax', 'medianViewsMin', 'averageViewsMin', 'engagementMin', 'sort');
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->platform !== '' || $this->category !== '' || $this->claimed !== '' || $this->verified
            || $this->followersMin !== '' || $this->followersMax !== '' || $this->medianViewsMin !== '' || $this->averageViewsMin !== '' || $this->engagementMin !== '';
    }

    protected function query(): Builder
    {
        $platform = Platform::tryFrom($this->platform);

        return Creator::query()
            ->active()
            ->with(['category', 'socialAccounts.performanceMetrics', 'socialAccounts.audience'])
            ->when($this->search !== '', function (Builder $q) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($this->search)).'%';
                $handle = '%'.ltrim(trim($this->search), '@').'%';

                $q->where(function (Builder $q) use ($term, $handle) {
                    $q->where('name', 'like', $term)
                        ->orWhereHas('socialAccounts', fn (Builder $a) => $a->where('handle', 'like', $handle))
                        ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $term));
                });
            })
            ->when($platform, function (Builder $q) use ($platform) {
                $q->whereHas('socialAccounts', function (Builder $a) use ($platform) {
                    $a->where('platform', $platform)
                        ->when($this->followersMin !== '', fn ($a) => $a->where('follower_count', '>=', (int) $this->followersMin))
                        ->when($this->followersMax !== '', fn ($a) => $a->where('follower_count', '<=', (int) $this->followersMax));
                });
            })
            ->when(! $platform && $this->followersMin !== '', fn (Builder $q) => $q->where('follower_count', '>=', (int) $this->followersMin))
            ->when(! $platform && $this->followersMax !== '', fn (Builder $q) => $q->where('follower_count', '<=', (int) $this->followersMax))
            ->when($this->category !== '', fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $this->category)))
            ->when($this->claimed === 'claimed', fn (Builder $q) => $q->claimed())
            ->when($this->claimed === 'unclaimed', fn (Builder $q) => $q->unclaimed())
            ->when($this->verified, fn (Builder $q) => $q->where('has_verified_metrics', true))
            ->when($this->medianViewsMin !== '', fn (Builder $q) => $q->where('median_views', '>=', (int) $this->medianViewsMin))
            ->when($this->averageViewsMin !== '', fn (Builder $q) => $q->where('average_views', '>=', (int) $this->averageViewsMin))
            ->when($this->engagementMin !== '', fn (Builder $q) => $q->where('engagement_rate', '>=', (float) $this->engagementMin))
            ->tap(fn (Builder $q) => $this->applySort($q));
    }

    private function applySort(Builder $query): void
    {
        match ($this->sort) {
            'median_views' => $query->orderByRaw('median_views is null')->orderByDesc('median_views')->orderByDesc('follower_count'),
            'watched' => $query->orderByRaw('average_view_percentage is null')->orderByDesc('average_view_percentage')->orderByDesc('follower_count'),
            'engagement' => $query->orderByRaw('engagement_rate is null')->orderByDesc('engagement_rate')->orderByDesc('follower_count'),
            'newest' => $query->orderByDesc('id'),
            default => $query->orderByDesc('follower_count')->orderBy('name'),
        };
    }

    /** Number of creators per platform, for the tabs. */
    protected function platformCounts(): array
    {
        return CreatorSocialAccount::query()
            ->whereHas('creator', fn ($q) => $q->active())
            ->selectRaw('platform, count(distinct creator_id) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform')
            ->all();
    }

    public function render(): View
    {
        return view('livewire.marketplace', [
            'creators' => $this->query()->paginate(24),
            'categories' => CreatorCategory::orderBy('sort_order')->orderBy('name')->get(),
            'platforms' => Platform::enabled(),
            'platformCounts' => $this->platformCounts(),
            'activePlatform' => Platform::tryFrom($this->platform),
            'total' => Creator::active()->count(),
            'verifiedTotal' => Creator::active()->where('has_verified_metrics', true)->count(),
        ])->layout('components.layouts.app', [
            'band' => true,
            'description' => 'Browse creators on YouTube, Instagram and X. Filter by platform, category, audience size and verified numbers like median views and engagement.',
            // Filters and pages are all the same list to a search engine.
            'canonical' => route('creators.index', array_filter(['platform' => $this->platform, 'category' => $this->category])),
        ])->title('Browse creators');
    }
}
