<?php

namespace App\Livewire;

use App\Enums\Platform;
use App\Enums\RankMetric;
use App\Models\CreatorCategory;
use App\Services\Metrics\CreatorRankings;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The home page ranking. Verified creators only; the visitor picks what to rank by.
 */
class TopPerformers extends Component
{
    #[Url(as: 'rank', except: 'median_views')]
    public string $metric = 'median_views';

    #[Url(as: 'on', except: '')]
    public string $platform = '';

    #[Url(as: 'in', except: '')]
    public string $category = '';

    public int $limit = 20;

    public function updatedPlatform(): void
    {
        // Watched / first-week views only exist for YouTube; fall back when they stop making sense.
        if (! $this->rankMetric()->availableFor($this->selectedPlatform())) {
            $this->metric = RankMetric::MedianViews->value;
        }
    }

    public function rankMetric(): RankMetric
    {
        return RankMetric::tryFrom($this->metric) ?? RankMetric::MedianViews;
    }

    public function selectedPlatform(): ?Platform
    {
        return Platform::tryFrom($this->platform);
    }

    public function render(CreatorRankings $rankings): View
    {
        $metric = $this->rankMetric();
        $platform = $this->selectedPlatform();
        $category = $this->category !== '' ? CreatorCategory::where('slug', $this->category)->first() : null;

        $creators = $rankings->ranked($metric, $platform, $category?->id)
            ->with(['category', 'socialAccounts.performanceMetrics', 'socialAccounts.audience'])
            ->limit($this->limit)
            ->get();

        return view('livewire.top-performers', [
            'active' => $metric,
            'metrics' => collect(RankMetric::cases())->filter(fn (RankMetric $m) => $m->availableFor($platform)),
            'platforms' => Platform::cases(),
            'categories' => CreatorCategory::orderBy('sort_order')->get(),
            'creators' => $creators,
            'total' => $rankings->ranked($metric, $platform, $category?->id)->count(),
        ]);
    }
}
