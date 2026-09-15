<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Enums\MetricWindow;
use App\Enums\Platform;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Services\Metrics\PerformanceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_views_reach_engagement_and_watch_time(): void
    {
        $sample = collect([
            $this->content(['views' => 100, 'reach' => 80, 'likes' => 5, 'comments' => 1, 'average_watch_time_seconds' => 10, 'average_view_percentage' => 40]),
            $this->content(['views' => 300, 'reach' => 200, 'likes' => 9, 'comments' => 0, 'shares' => 3, 'average_watch_time_seconds' => 20, 'average_view_percentage' => 50]),
            $this->content(['views' => 200, 'reach' => 120, 'likes' => 10, 'average_watch_time_seconds' => 30, 'average_view_percentage' => 60]),
        ]);

        $result = (new PerformanceCalculator)->aggregate($sample);

        $this->assertSame(200.0, $result['average_views']);
        $this->assertSame(200.0, $result['median_views']);
        $this->assertSame(133.0, $result['average_reach']);
        $this->assertSame(120.0, $result['median_reach']);
        $this->assertSame(20.0, $result['average_watch_time']);
        $this->assertSame(50.0, $result['average_view_percentage']);
        // (6/100 + 12/300 + 10/200) / 3 = (6% + 4% + 5%) / 3 = 5%
        $this->assertSame(5.0, $result['engagement_rate']);
        $this->assertSame(8.0, $result['extra']['average_likes']);
        $this->assertSame(600, $result['extra']['total_views']);
    }

    public function test_median_is_robust_to_a_single_viral_item(): void
    {
        $sample = collect(array_map(fn ($v) => $this->content(['views' => $v]), [21000, 25000, 30000, 28000, 2_400_000]));

        $result = (new PerformanceCalculator)->aggregate($sample);

        $this->assertSame(28000.0, $result['median_views']);
        $this->assertSame(500800.0, $result['average_views']);
    }

    public function test_it_stores_separate_rows_per_content_type_and_window(): void
    {
        $account = CreatorSocialAccount::factory()->platform(Platform::YouTube, 'chan')->connected()->create();

        foreach (range(1, 25) as $i) {
            SocialContent::factory()->for($account, 'socialAccount')->withMetrics(['views' => $i * 1000])->create([
                'content_type' => ContentType::Video,
                'published_at' => now()->subDays($i * 2),
            ]);
        }

        SocialContent::factory()->for($account, 'socialAccount')->withMetrics(['views' => 999_999])->create([
            'content_type' => ContentType::Short,
            'published_at' => now()->subDay(),
        ]);

        (new PerformanceCalculator)->calculateAndStore($account);

        $videoLast10 = $account->performanceFor(ContentType::Video, MetricWindow::Last10);
        $videoLast30Days = $account->performanceFor(ContentType::Video, MetricWindow::Last30Days);
        $shortLast20 = $account->performanceFor(ContentType::Short, MetricWindow::Last20);

        $this->assertSame(10, $videoLast10->sample_size);
        $this->assertSame(5500, $videoLast10->median_views); // newest 10: 1K..10K
        $this->assertSame(14, $videoLast30Days->sample_size); // published 2..28 days ago
        $this->assertSame(1, $shortLast20->sample_size);
        $this->assertSame(999_999, $shortLast20->median_views);
        // The viral short never leaks into the video numbers.
        $this->assertLessThan(999_999, $account->performanceFor(ContentType::Video, MetricWindow::Last30)->average_views);
    }

    private function content(array $metrics): SocialContent
    {
        return new SocialContent(['metrics' => $metrics, 'published_at' => now()]);
    }
}
