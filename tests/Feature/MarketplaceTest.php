<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Livewire\Marketplace;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorPerformanceMetric;
use App\Models\CreatorSocialAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private Creator $john;

    private Creator $lena;

    private Creator $marcus;

    protected function setUp(): void
    {
        parent::setUp();

        $finance = CreatorCategory::factory()->create(['name' => 'Finance', 'slug' => 'finance']);
        $fitness = CreatorCategory::factory()->create(['name' => 'Fitness', 'slug' => 'fitness']);

        $this->john = Creator::factory()->claimedBy()->create([
            'name' => 'John Smith', 'creator_category_id' => $finance->id,
            'follower_count' => 124_000, 'median_views' => 73_000, 'average_views' => 91_000, 'engagement_rate' => 4.6,
            'has_verified_metrics' => true, 'metrics_synced_at' => now(),
        ]);
        $johnYouTube = CreatorSocialAccount::factory()->for($this->john)->platform(Platform::YouTube, 'johnsmith')->connected()->create(['follower_count' => 124_000]);
        CreatorPerformanceMetric::create([
            'creator_id' => $this->john->id, 'creator_social_account_id' => $johnYouTube->id, 'platform' => Platform::YouTube,
            'content_type' => 'video', 'calculation_window' => 'last_20', 'sample_size' => 20,
            'median_views' => 73_000, 'average_views' => 91_000, 'engagement_rate' => 4.6, 'average_view_percentage' => 46, 'calculated_at' => now(),
        ]);

        $this->lena = Creator::factory()->create(['name' => 'Lena Fischer', 'creator_category_id' => $fitness->id, 'follower_count' => 182_000]);
        CreatorSocialAccount::factory()->for($this->lena)->platform(Platform::Instagram, 'lena.fit')->create(['follower_count' => 182_000]);

        $this->marcus = Creator::factory()->create(['name' => 'Marcus Reid', 'follower_count' => 91_000]);
        CreatorSocialAccount::factory()->for($this->marcus)->platform(Platform::X, 'marcusreid')->create(['follower_count' => 91_000]);

        Creator::factory()->create(['name' => 'Hidden Person', 'status' => 'hidden']);
    }

    public function test_it_lists_active_creators_only(): void
    {
        Livewire::test(Marketplace::class)
            ->assertSee('John Smith')
            ->assertSee('Lena Fischer')
            ->assertSee('Marcus Reid')
            ->assertDontSee('Hidden Person');
    }

    public function test_search_matches_name_handle_and_category(): void
    {
        Livewire::test(Marketplace::class)->set('search', 'lena')->assertSee('Lena Fischer')->assertDontSee('John Smith');
        Livewire::test(Marketplace::class)->set('search', '@marcusreid')->assertSee('Marcus Reid')->assertDontSee('Lena Fischer');
        Livewire::test(Marketplace::class)->set('search', 'finance')->assertSee('John Smith')->assertDontSee('Marcus Reid');
    }

    public function test_platform_and_category_filters(): void
    {
        Livewire::test(Marketplace::class)->set('platform', 'instagram')->assertSee('Lena Fischer')->assertDontSee('John Smith')->assertDontSee('Marcus Reid');
        Livewire::test(Marketplace::class)->set('category', 'finance')->assertSee('John Smith')->assertDontSee('Lena Fischer');
    }

    public function test_claimed_and_verified_filters(): void
    {
        Livewire::test(Marketplace::class)->set('claimed', 'claimed')->assertSee('John Smith')->assertDontSee('Lena Fischer');
        Livewire::test(Marketplace::class)->set('claimed', 'unclaimed')->assertSee('Lena Fischer')->assertDontSee('John Smith');
        Livewire::test(Marketplace::class)->set('verified', true)->assertSee('John Smith')->assertDontSee('Marcus Reid');
    }

    public function test_follower_range_filter(): void
    {
        Livewire::test(Marketplace::class)->set('followersMin', 100_000)->assertSee('John Smith')->assertSee('Lena Fischer')->assertDontSee('Marcus Reid');
        Livewire::test(Marketplace::class)->set('followersMax', 100_000)->assertSee('Marcus Reid')->assertDontSee('John Smith');
    }

    public function test_performance_filters_only_match_verified_profiles(): void
    {
        Livewire::test(Marketplace::class)->set('medianViewsMin', 50_000)->assertSee('John Smith')->assertDontSee('Lena Fischer')->assertDontSee('Marcus Reid');
        Livewire::test(Marketplace::class)->set('medianViewsMin', 80_000)->assertSee('No creators match');
        Livewire::test(Marketplace::class)->set('averageViewsMin', 90_000)->assertSee('John Smith');
        Livewire::test(Marketplace::class)->set('engagementMin', 5)->assertDontSee('John Smith');
        Livewire::test(Marketplace::class)->set('engagementMin', 4)->assertSee('John Smith');
    }

    public function test_sorting_by_median_views_puts_verified_profiles_first(): void
    {
        Livewire::test(Marketplace::class)->set('sort', 'median_views')->assertSeeInOrder(['John Smith', 'Lena Fischer']);
        Livewire::test(Marketplace::class)->set('sort', 'followers')->assertSeeInOrder(['Lena Fischer', 'John Smith', 'Marcus Reid']);
    }

    public function test_filters_live_in_the_url(): void
    {
        $this->get(route('creators.index', ['platform' => 'x', 'q' => 'marcus']))
            ->assertOk()
            ->assertSee('Marcus Reid')
            ->assertDontSee('John Smith');
    }

    public function test_cards_distinguish_verified_metrics_from_public_data(): void
    {
        Livewire::test(Marketplace::class)
            ->set('view', 'cards')
            ->assertSeeHtml('Verified metrics')
            ->assertSee('73K') // John's median views
            ->assertSee('46%')
            ->assertSee('Public info only');
    }
}
