<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Enums\ProfileState;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_unclaimed(): void
    {
        $creator = Creator::factory()->create();
        CreatorSocialAccount::factory()->for($creator)->create();

        $this->assertSame(ProfileState::Unclaimed, $creator->profileState());
        $this->get(route('creators.show', $creator))->assertSee('Unclaimed')->assertSee('Public info only')->assertDontSee('Verified through');
    }

    public function test_claimed_without_connection(): void
    {
        $creator = Creator::factory()->claimedBy()->create();
        CreatorSocialAccount::factory()->for($creator)->create();

        $this->assertSame(ProfileState::Claimed, $creator->profileState());
        $this->get(route('creators.show', $creator))->assertSee('no account connected')->assertDontSee('Is this you?');
    }

    public function test_verified_metrics(): void
    {
        $creator = Creator::factory()->claimedBy()->create(['has_verified_metrics' => true, 'metrics_synced_at' => now()]);
        CreatorSocialAccount::factory()->for($creator)->platform(Platform::YouTube, 'x')->connected()->create();

        $this->assertSame(ProfileState::VerifiedMetrics, $creator->profileState());
        $this->get(route('creators.show', $creator))->assertSee('Verified metrics')->assertSeeInOrder(['Verified through', 'YouTube']);
    }

    public function test_metrics_outdated(): void
    {
        config(['social.sync.stale_after_days' => 7]);
        $creator = Creator::factory()->claimedBy()->create(['has_verified_metrics' => true, 'metrics_synced_at' => now()->subDays(8)]);
        CreatorSocialAccount::factory()->for($creator)->connected()->create(['last_synced_at' => now()->subDays(8)]);

        $this->assertSame(ProfileState::MetricsOutdated, $creator->profileState());
        $this->get(route('creators.show', $creator))->assertSee('Metrics outdated');
    }

    public function test_needs_reconnection(): void
    {
        $creator = Creator::factory()->claimedBy()->create(['has_verified_metrics' => true, 'metrics_synced_at' => now()]);
        CreatorSocialAccount::factory()->for($creator)->connected()->create(['connection_status' => ConnectionStatus::NeedsReconnection]);

        $this->assertSame(ProfileState::NeedsReconnection, $creator->profileState());
        $this->get(route('creators.show', $creator))->assertSee('Needs reconnection');
    }

    public function test_importing_state_polls_the_page(): void
    {
        $creator = Creator::factory()->claimedBy()->create();
        CreatorSocialAccount::factory()->for($creator)->connected()->create(['connection_status' => ConnectionStatus::Importing, 'last_synced_at' => null]);

        $this->get(route('creators.show', $creator))->assertSee('Pulling in content and stats')->assertSee('wire:poll', false);
    }

    public function test_seo_title_and_canonical(): void
    {
        $creator = Creator::factory()->create(['name' => 'John Smith', 'slug' => 'john-smith']);
        CreatorSocialAccount::factory()->for($creator)->platform(Platform::YouTube, 'johnsmith')->create();

        $this->get('/creators/john-smith')
            ->assertSee('<title>John Smith YouTube Stats &amp; Verified Creator Metrics', false)
            ->assertSee('<link rel="canonical" href="'.route('creators.show', 'john-smith').'">', false);
    }

    public function test_merged_profiles_redirect_to_the_canonical_one(): void
    {
        $target = Creator::factory()->create();
        $duplicate = Creator::factory()->create(['status' => 'merged', 'merged_into_creator_id' => $target->id]);

        $this->get(route('creators.show', $duplicate))->assertRedirect(route('creators.show', $target));
        $this->get(route('creators.show', Creator::factory()->create(['status' => 'hidden'])))->assertNotFound();
    }
}
