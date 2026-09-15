<?php

namespace Tests\Unit;

use App\Services\Metrics\Statistics;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
    public function test_median_of_odd_count(): void
    {
        $this->assertSame(28000.0, Statistics::median([21000, 25000, 30000, 28000, 2_400_000]));
    }

    public function test_median_of_even_count_averages_the_middle_pair(): void
    {
        $this->assertSame(27500.0, Statistics::median([25000, 30000, 21000, 2_400_000]));
    }

    public function test_median_ignores_nulls_and_handles_empty(): void
    {
        $this->assertSame(10.0, Statistics::median([null, 10, null]));
        $this->assertNull(Statistics::median([]));
        $this->assertNull(Statistics::median([null]));
    }

    public function test_a_viral_outlier_moves_the_mean_but_not_the_median(): void
    {
        $normal = [21000, 25000, 30000, 28000, 26000];
        $withViral = [...$normal, 2_400_000];

        $this->assertSame(26000.0, Statistics::median($normal));
        $this->assertSame(27000.0, Statistics::median($withViral));
        $this->assertGreaterThan(400_000, Statistics::mean($withViral));
    }
}
