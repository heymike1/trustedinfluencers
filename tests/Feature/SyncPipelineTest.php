<?php

namespace Tests\Feature;

use App\Actions\Sync\StartAccountSync;
use App\Enums\ConnectionStatus;
use App\Enums\ContentType;
use App\Enums\MetricWindow;
use App\Enums\Platform;
use App\Enums\ProfileState;
use App\Jobs\SyncCreatorSocialProfile;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Exceptions\ConnectorException;
use App\Social\Exceptions\ReconnectionRequiredException;
use App\Social\Fake\FakeConnector;
use App\Social\OAuth\TokenManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SyncPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_chain_imports_profile_content_metrics_and_performance(): void
    {
        $creator = Creator::factory()->claimedBy()->create();
        $account = CreatorSocialAccount::factory()->for($creator)->platform(Platform::YouTube, 'johnsmith')->connected()->create(['last_synced_at' => null]);

        app(StartAccountSync::class)->handle($account); // queue is sync in tests

        $account->refresh();
        $creator->refresh();

        $this->assertSame(ConnectionStatus::Connected, $account->connection_status);
        $this->assertNotNull($account->last_synced_at);
        $this->assertSame(124_000, $account->follower_count);
        $this->assertSame('John Smith', $account->display_name);

        $this->assertGreaterThan(0, $account->contents()->count());
        $this->assertTrue($account->contents()->whereNotNull('metrics')->exists());
        // One snapshot from the profile sync, one per content item, one for the audience breakdown.
        $this->assertSame($account->contents()->count() + 2, $account->snapshots()->count());

        $audience = $account->audience;
        $this->assertNotNull($audience);
        $this->assertEquals(38, $audience->countries['GB']);
        $this->assertEquals(['mobile' => 58, 'desktop' => 31, 'tv' => 8, 'tablet' => 3], $audience->devices);
        $this->assertNotNull($audience->metric('views_28d'));

        $newest = $account->contents()->orderByDesc('published_at')->first();
        $this->assertCount(21, $newest->retentionCurve());
        $this->assertEquals(100, $newest->retentionCurve()[0]);
        $this->assertNotNull($newest->metric('views_7d'));

        $video = $account->performanceFor(ContentType::Video, MetricWindow::Last20);
        $this->assertNotNull($video);
        $this->assertGreaterThan(0, $video->median_views);
        $this->assertNotNull($video->average_view_percentage);
        $this->assertCount(21, $video->extra('retention_curve'));
        $this->assertCount(30, $video->extra('velocity_curve'));
        $this->assertEquals(100, $video->extra('velocity_curve')[29]);
        $this->assertGreaterThan(0, $video->extra('median_views_7d'));
        $this->assertGreaterThan(0, $video->extra('posts_per_month'));
        $this->assertGreaterThan(0, $video->extra('likes_per_1k'));
        $this->assertNotNull($account->performanceFor(ContentType::Short, MetricWindow::Last10));

        $this->assertTrue($creator->has_verified_metrics);
        $this->assertSame($video->median_views, $creator->median_views);
        $this->assertSame($video->average_view_percentage, $creator->average_view_percentage);
        $this->assertSame(Platform::YouTube, $creator->primary_platform);
        $this->assertSame(ProfileState::VerifiedMetrics, $creator->profileState());
    }

    public function test_repeated_syncs_add_snapshots_instead_of_overwriting_history(): void
    {
        $creator = Creator::factory()->claimedBy()->create();
        $account = CreatorSocialAccount::factory()->for($creator)->platform(Platform::X, 'marcusreid')->connected()->create();

        app(StartAccountSync::class)->handle($account);
        $after1 = $account->snapshots()->count();

        app(StartAccountSync::class)->handle($account);
        $after2 = $account->snapshots()->count();

        $this->assertGreaterThan($after1, $after2);
        $this->assertSame($account->contents()->count(), 30);
        $this->assertSame(1, $account->performanceMetrics()->where('calculation_window', MetricWindow::Last20)->count());
    }

    public function test_a_revoked_token_marks_the_account_as_needing_reconnection(): void
    {
        $creator = Creator::factory()->claimedBy()->create(['has_verified_metrics' => true]);
        $account = CreatorSocialAccount::factory()->for($creator)->platform(Platform::Instagram, 'lena.fit')->connected()->create();

        $connector = Mockery::mock(SocialPlatformConnector::class);
        $connector->shouldReceive('syncProfile')->once()->andThrow(new ReconnectionRequiredException('Token revoked'));
        app(ConnectorManager::class)->fake(Platform::Instagram, $connector);

        try {
            (new SyncCreatorSocialProfile($account))->handle(app(ConnectorManager::class), app(TokenManager::class));
        } catch (\Throwable) {
            // The job calls fail(); outside a worker that surfaces as an exception.
        }

        $account->refresh();
        $this->assertSame(ConnectionStatus::NeedsReconnection, $account->connection_status);
        $this->assertSame('Token revoked', $account->last_sync_error);
        $this->assertSame(ProfileState::NeedsReconnection, $creator->fresh()->profileState());
    }

    public function test_an_api_failure_marks_the_sync_as_failed(): void
    {
        $account = CreatorSocialAccount::factory()->platform(Platform::YouTube, 'x')->connected()->create();

        $connector = Mockery::mock(SocialPlatformConnector::class);
        $connector->shouldReceive('syncProfile')->andThrow(new ConnectorException('YouTube: loading channel failed with HTTP 500'));
        app(ConnectorManager::class)->fake(Platform::YouTube, $connector);

        $job = new SyncCreatorSocialProfile($account);
        $job->failed(new ConnectorException('YouTube: loading channel failed with HTTP 500'));

        $this->assertSame(ConnectionStatus::SyncFailed, $account->fresh()->connection_status);
        $this->assertStringContainsString('HTTP 500', $account->fresh()->last_sync_error);
    }

    public function test_expiring_tokens_are_refreshed_before_syncing(): void
    {
        $account = CreatorSocialAccount::factory()->platform(Platform::YouTube, 'johnsmith')->connected()->create([
            'access_token' => FakeConnector::tokenFor(Platform::YouTube, 'johnsmith'),
            'token_expires_at' => now()->addMinute(),
        ]);
        $oldToken = $account->access_token;

        app(StartAccountSync::class)->handle($account);

        $account->refresh();
        $this->assertNotSame($oldToken, $account->access_token);
        $this->assertTrue($account->token_expires_at->gt(now()->addMinutes(30)));
    }

    public function test_tokens_are_encrypted_at_rest_and_hidden_from_serialisation(): void
    {
        $account = CreatorSocialAccount::factory()->connected()->create(['access_token' => 'secret-token']);

        $raw = \DB::table('creator_social_accounts')->where('id', $account->id)->value('access_token');

        $this->assertNotSame('secret-token', $raw);
        $this->assertSame('secret-token', $account->fresh()->access_token);
        $this->assertArrayNotHasKey('access_token', $account->toArray());
        $this->assertArrayNotHasKey('refresh_token', $account->toArray());
    }
}
