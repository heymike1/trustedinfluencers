<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Enums\RankMetric;
use App\Livewire\TopPerformers;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Services\Metrics\CreatorRankings;
use App\Services\Metrics\PerformanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InsightsAndRankingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_curves_are_averaged_point_by_point(): void
    {
        $a = array_map(fn ($i) => 100 - $i * 4, range(0, 20)); // 100 → 20
        $b = array_map(fn ($i) => 100 - $i * 2, range(0, 20)); // 100 → 60

        $curve = (new PerformanceCalculator)->averageCurve(collect([$a, $b, [1, 2, 3]])); // short one ignored

        $this->assertCount(21, $curve);
        $this->assertEquals(100, $curve[0]);
        $this->assertEquals(40, $curve[20]);
    }

    public function test_velocity_curve_is_cumulative_and_ignores_young_videos(): void
    {
        $daily = array_fill(0, 30, 10); // 300 views, evenly
        $daily[0] = 100; // strong first day → 390 total

        $curve = (new PerformanceCalculator)->velocityCurve(collect([$daily, [50, 50, 50]]));

        $this->assertCount(30, $curve);
        $this->assertEquals(round(100 / 390 * 100, 1), $curve[0]);
        $this->assertEquals(100, $curve[29]);
        $this->assertNull((new PerformanceCalculator)->velocityCurve(collect([[1, 2, 3]])));
    }

    public function test_cadence_counts_posts_per_month_and_longest_gap(): void
    {
        $sample = collect([
            new SocialContent(['published_at' => now()->subDays(0)]),
            new SocialContent(['published_at' => now()->subDays(7)]),
            new SocialContent(['published_at' => now()->subDays(30)]),
            new SocialContent(['published_at' => now()->subDays(60)]),
        ]);

        $cadence = (new PerformanceCalculator)->cadence($sample);

        $this->assertEquals(2.0, $cadence['posts_per_month']);
        $this->assertSame(30, $cadence['longest_gap_days']);
    }

    public function test_rankings_place_creators_among_verified_ones_only(): void
    {
        $finance = CreatorCategory::factory()->create(['name' => 'Finance']);
        $top = $this->verifiedCreator(median: 100_000, watched: 30, category: $finance);
        $mid = $this->verifiedCreator(median: 60_000, watched: 55, category: $finance);
        $this->verifiedCreator(median: 80_000, watched: 40);
        Creator::factory()->create(['median_views' => 999_999, 'has_verified_metrics' => false]); // unverified, ignored

        $rankings = new CreatorRankings;

        $this->assertSame(['rank' => 1, 'total' => 3], $rankings->position($top, RankMetric::MedianViews));
        $this->assertSame(['rank' => 3, 'total' => 3], $rankings->position($mid, RankMetric::MedianViews));
        $this->assertSame(['rank' => 1, 'total' => 2], $rankings->position($mid, RankMetric::Watched, null, $finance->id));
        $this->assertNull($rankings->position(Creator::factory()->create(), RankMetric::MedianViews));
    }

    public function test_top_performers_reranks_when_the_metric_changes(): void
    {
        $bigButWeak = $this->verifiedCreator(median: 100_000, watched: 20, name: 'Big But Weak');
        $smallButGreat = $this->verifiedCreator(median: 40_000, watched: 60, name: 'Small But Great');

        Livewire::test(TopPerformers::class)
            ->assertSeeInOrder(['Big But Weak', 'Small But Great'])
            ->set('metric', 'watched')
            ->assertSeeInOrder(['Small But Great', 'Big But Weak'])
            ->set('platform', 'instagram') // "watched" is YouTube-only → falls back to median views
            ->assertSet('metric', 'median_views');
    }

    public function test_the_profile_band_shows_ranks_for_verified_creators(): void
    {
        $creator = $this->verifiedCreator(median: 50_000, watched: 45, name: 'Ranked Person');

        $this->get(route('creators.show', $creator))
            ->assertOk()
            ->assertSee('Top performers')
            ->assertSee('#1')
            ->assertSee('most watched');
    }

    public function test_the_directory_table_switches_columns_per_platform(): void
    {
        $this->verifiedCreator(median: 50_000, watched: 45, platform: Platform::YouTube, name: 'Tube Person');
        $this->verifiedCreator(median: 50_000, watched: null, platform: Platform::Instagram, name: 'Gram Person');
        $this->verifiedCreator(median: 50_000, watched: null, platform: Platform::X, name: 'X Person');

        $this->get(route('creators.index', ['platform' => 'youtube']))->assertOk()->assertSee('Watch curve')->assertSee('Tube Person')->assertDontSee('Gram Person');
        $this->get(route('creators.index', ['platform' => 'instagram']))->assertOk()->assertSee('New viewers')->assertSee('Gram Person')->assertDontSee('Watch curve');
        $this->get(route('creators.index', ['platform' => 'x']))->assertOk()->assertSee('Link clicks')->assertSee('X Person')->assertDontSee('Top country');
    }

    private function verifiedCreator(int $median, ?float $watched, ?CreatorCategory $category = null, Platform $platform = Platform::YouTube, ?string $name = null): Creator
    {
        $creator = Creator::factory()->claimedBy()->create(array_filter([
            'name' => $name,
            'creator_category_id' => $category?->id,
            'has_verified_metrics' => true,
            'metrics_synced_at' => now(),
            'follower_count' => 100_000,
            'median_views' => $median,
            'average_views' => $median * 2,
            'engagement_rate' => 4.0,
            'average_view_percentage' => $watched,
            'primary_platform' => $platform,
        ], fn ($v) => $v !== null));

        $account = CreatorSocialAccount::factory()->for($creator)->platform($platform, 'h'.$creator->id)->connected()->create(['follower_count' => 100_000]);

        $account->performanceMetrics()->create([
            'creator_id' => $creator->id,
            'platform' => $platform,
            'content_type' => $platform->primaryContentType(),
            'calculation_window' => 'last_20',
            'sample_size' => 20,
            'median_views' => $median,
            'average_views' => $median * 2,
            'median_reach' => $platform === Platform::Instagram ? $median : null,
            'median_impressions' => $platform === Platform::X ? $median : null,
            'engagement_rate' => 4.0,
            'average_view_percentage' => $watched,
            'extra' => ['retention_curve' => array_fill(0, 21, 50.0)],
            'calculated_at' => now(),
        ]);

        return $creator;
    }
}
