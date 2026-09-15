<?php

namespace Tests\Feature;

use App\Enums\ContentType;
use App\Models\SocialContent;
use App\Social\Connectors\InstagramConnector;
use App\Social\Connectors\XConnector;
use App\Social\Connectors\YouTubeConnector;
use App\Social\Data\AccountContext;
use App\Social\Data\OAuthRequest;
use App\Social\Data\OAuthTokens;
use App\Social\Exceptions\ReconnectionRequiredException;
use App\Social\Login\LiveGoogleLoginProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The live connectors against recorded response shapes, so a renamed field is caught here
 * rather than in production. No network: every request is faked.
 */
class LiveConnectorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['social.driver' => 'live', 'social.platforms.youtube.client_id' => 'yt-id', 'social.platforms.youtube.client_secret' => 'yt-secret',
            'social.platforms.instagram.client_id' => 'ig-id', 'social.platforms.instagram.client_secret' => 'ig-secret',
            'social.platforms.x.client_id' => 'x-id', 'social.platforms.x.client_secret' => 'x-secret',
            'social.google_login.client_id' => 'g-id', 'social.google_login.client_secret' => 'g-secret']);
    }

    private function request(): OAuthRequest
    {
        return new OAuthRequest('state', 'verifier', 'https://app.test/callback');
    }

    public function test_youtube_exchanges_the_code_and_reads_the_channel_identity(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'at', 'refresh_token' => 'rt', 'expires_in' => 3600, 'scope' => 'a b']),
            'www.googleapis.com/youtube/v3/channels*' => Http::response(['items' => [[
                'id' => 'UC123', 'snippet' => ['title' => 'John', 'customUrl' => '@JohnSmith', 'thumbnails' => ['medium' => ['url' => 'https://img/j.jpg']]],
                'statistics' => ['subscriberCount' => '124000', 'viewCount' => '5', 'videoCount' => '3'],
                'contentDetails' => ['relatedPlaylists' => ['uploads' => 'UU123']],
            ]]]),
        ]);

        $connector = new YouTubeConnector;
        $tokens = $connector->exchangeCode('code', $this->request());
        $identity = $connector->identity($tokens);

        $this->assertSame('rt', $tokens->refreshToken);
        $this->assertSame(['a', 'b'], $tokens->scopes);
        $this->assertSame('UC123', $identity->providerAccountId);
        $this->assertSame('johnsmith', $identity->handle);
        $this->assertSame(124000, $identity->followerCount);

        Http::assertSent(fn ($req) => str_contains($req->url(), 'oauth2.googleapis.com/token') && $req['code_verifier'] === 'verifier' && $req['grant_type'] === 'authorization_code');
        $this->assertStringContainsString('yt-analytics.readonly', $connector->authorizationUrl($this->request()));
    }

    public function test_youtube_analytics_rows_map_to_normalised_metrics_and_insights(): void
    {
        Http::fake([
            'youtubeanalytics.googleapis.com/v2/reports?*dimensions=video*' => Http::response([
                'columnHeaders' => [['name' => 'video'], ['name' => 'views'], ['name' => 'likes'], ['name' => 'comments'], ['name' => 'shares'], ['name' => 'estimatedMinutesWatched'], ['name' => 'averageViewDuration'], ['name' => 'averageViewPercentage']],
                'rows' => [['vid1', 1000, 50, 5, 2, 100, 360.5, 46.2]],
            ]),
            'youtubeanalytics.googleapis.com/v2/reports?*elapsedVideoTimeRatio*' => Http::response([
                'columnHeaders' => [['name' => 'elapsedVideoTimeRatio'], ['name' => 'audienceWatchRatio']],
                'rows' => array_map(fn ($i) => [$i / 100, 1 - $i / 125], range(0, 99)),
            ]),
            'youtubeanalytics.googleapis.com/v2/reports?*dimensions=day*' => Http::response([
                'columnHeaders' => [['name' => 'day'], ['name' => 'views']],
                'rows' => [['2026-01-01', 300], ['2026-01-02', 200], ['2026-01-03', 100]],
            ]),
            'youtubeanalytics.googleapis.com/v2/reports?*ageGroup*' => Http::response([
                'columnHeaders' => [['name' => 'ageGroup'], ['name' => 'gender'], ['name' => 'viewerPercentage']],
                'rows' => [['age25-34', 'male', 30.0], ['age25-34', 'female', 11.0], ['age18-24', 'male', 9.0]],
            ]),
            'youtubeanalytics.googleapis.com/v2/reports?*dimensions=country*' => Http::response(['columnHeaders' => [['name' => 'country'], ['name' => 'views']], 'rows' => [['GB', 380], ['US', 240]]]),
            'youtubeanalytics.googleapis.com/v2/reports?*deviceType*' => Http::response(['columnHeaders' => [['name' => 'deviceType'], ['name' => 'views']], 'rows' => [['MOBILE', 58], ['DESKTOP', 42]]]),
            'youtubeanalytics.googleapis.com/v2/reports*' => Http::response(['columnHeaders' => [['name' => 'views'], ['name' => 'estimatedMinutesWatched'], ['name' => 'subscribersGained'], ['name' => 'subscribersLost']], 'rows' => [[5000, 600, 40, 3]]]),
        ]);

        $content = new SocialContent(['provider_content_id' => 'vid1', 'content_type' => ContentType::Video, 'published_at' => '2026-01-01']);
        $result = (new YouTubeConnector)->syncMetrics($this->context('UC123', 'johnsmith'), collect([$content]));

        $video = $result->contentMetrics[0];
        $this->assertSame('vid1', $video->providerContentId);
        $this->assertSame(1000, $video->metrics['views']);
        $this->assertSame(6000, $video->metrics['watch_time_seconds']);
        $this->assertSame(46.2, $video->metrics['average_view_percentage']);
        $this->assertSame(600, $video->metrics['views_7d']);
        $this->assertSame(300, $video->metrics['views_24h']);
        $this->assertCount(21, $video->insights['retention']);
        $this->assertEquals(100, $video->insights['retention'][0]);
        $this->assertSame([300, 200, 100], $video->insights['daily_views']);

        $audience = $result->audience;
        $this->assertEquals(41, $audience->age['25-34']);
        $this->assertEquals(39, $audience->gender['male']);
        $this->assertEquals(61.3, $audience->countries['GB']);
        $this->assertEquals(58, $audience->devices['mobile']);
        $this->assertSame(36000, $audience->accountMetrics['watch_time_seconds_28d']);
        $this->assertSame(40, $audience->accountMetrics['subscribers_gained_28d']);
    }

    public function test_youtube_treats_401_as_needing_reconnection(): void
    {
        Http::fake(['www.googleapis.com/youtube/v3/channels*' => Http::response(['error' => ['message' => 'Invalid Credentials']], 401)]);

        $this->expectException(ReconnectionRequiredException::class);
        (new YouTubeConnector)->identity(new OAuthTokens('expired'));
    }

    public function test_instagram_exchanges_for_a_long_lived_token_and_reads_media_insights(): void
    {
        Http::fake([
            'api.instagram.com/oauth/access_token' => Http::response(['access_token' => 'short', 'user_id' => 1, 'permissions' => ['instagram_business_basic']]),
            'graph.instagram.com/access_token*' => Http::response(['access_token' => 'long', 'expires_in' => 5184000]),
            'graph.instagram.com/v23.0/me?*' => Http::response(['id' => 'app-scoped', 'user_id' => '17841400000', 'username' => 'Lena.Fit', 'name' => 'Lena', 'followers_count' => 182000, 'media_count' => 612]),
            'graph.instagram.com/v23.0/me/media*' => Http::response(['data' => [
                ['id' => 'm1', 'media_type' => 'VIDEO', 'media_product_type' => 'REELS', 'caption' => 'Rest days', 'permalink' => 'https://instagram.com/reel/x', 'thumbnail_url' => 'https://img/1.jpg', 'timestamp' => '2026-09-13T10:00:00+0000', 'like_count' => 4100, 'comments_count' => 90],
                ['id' => 's1', 'media_type' => 'IMAGE', 'media_product_type' => 'STORY', 'timestamp' => '2026-09-13T11:00:00+0000'],
            ]]),
            'graph.instagram.com/v23.0/m1/insights*' => Http::response(['data' => [
                ['name' => 'views', 'values' => [['value' => 241000]]], ['name' => 'reach', 'values' => [['value' => 188000]]],
                ['name' => 'saved', 'values' => [['value' => 4100]]], ['name' => 'ig_reels_avg_watch_time', 'values' => [['value' => 9100]]],
            ]]),
            'graph.instagram.com/v23.0/me/insights?*follower_demographics*breakdown=age*' => Http::response(['data' => [['total_value' => ['breakdowns' => [['results' => [['dimension_values' => ['25-34'], 'value' => 460], ['dimension_values' => ['18-24'], 'value' => 220]]]]]]]]),
            'graph.instagram.com/v23.0/me/insights?*follow_type*' => Http::response(['data' => [['total_value' => ['breakdowns' => [['results' => [['dimension_values' => ['FOLLOWER'], 'value' => 390], ['dimension_values' => ['NON_FOLLOWER'], 'value' => 610]]]]]]]]),
            'graph.instagram.com/v23.0/me/insights*' => Http::response(['data' => [['name' => 'reach', 'total_value' => ['value' => 2900000]], ['name' => 'profile_views', 'total_value' => ['value' => 41000]]]]),
        ]);

        $connector = new InstagramConnector;
        $tokens = $connector->exchangeCode('code', $this->request());
        $this->assertSame('long', $tokens->accessToken);
        $this->assertNull($tokens->refreshToken);

        $identity = $connector->identity($tokens);
        $this->assertSame('17841400000', $identity->providerAccountId); // the professional account id, not the app-scoped one
        $this->assertSame('lena.fit', $identity->handle);

        $context = new AccountContext('17841400000', 'lena.fit', $tokens);
        $items = $connector->syncContent($context, 30);
        $this->assertCount(1, $items); // stories are skipped
        $this->assertSame(ContentType::Reel, $items[0]->contentType);

        $result = $connector->syncMetrics($context, collect([new SocialContent(['provider_content_id' => 'm1', 'content_type' => ContentType::Reel])]));
        $reel = $result->contentMetrics[0]->metrics;
        $this->assertSame(241000, $reel['views']);
        $this->assertSame(4100, $reel['saves']);
        $this->assertSame(9.1, $reel['average_watch_time_seconds']);
        $this->assertEquals(67.6, $result->audience->age['25-34']);
        $this->assertEquals(61, $result->audience->followerType['non_follower']);
        $this->assertSame(41000, $result->audience->accountMetrics['profile_views_30d']);
    }

    public function test_x_uses_pkce_with_basic_auth_and_only_asks_private_metrics_for_recent_posts(): void
    {
        Http::fake([
            'api.x.com/2/oauth2/token' => Http::response(['access_token' => 'at', 'refresh_token' => 'rt', 'expires_in' => 7200, 'scope' => 'tweet.read users.read offline.access']),
            'api.x.com/2/users/me*' => Http::response(['data' => ['id' => '123', 'username' => 'MarcusReid', 'name' => 'Marcus', 'profile_image_url' => 'https://img/m_normal.jpg', 'public_metrics' => ['followers_count' => 91000]]]),
            'api.x.com/2/tweets?*non_public_metrics*' => Http::response(['data' => [['id' => 'new', 'non_public_metrics' => ['engagements' => 500, 'user_profile_clicks' => 40, 'url_link_clicks' => 12]]]]),
            'api.x.com/2/tweets?*' => Http::response(['data' => [
                ['id' => 'new', 'public_metrics' => ['impression_count' => 64000, 'like_count' => 900, 'reply_count' => 30, 'retweet_count' => 40, 'quote_count' => 5, 'bookmark_count' => 80]],
                ['id' => 'old', 'public_metrics' => ['impression_count' => 20000, 'like_count' => 100, 'reply_count' => 3, 'retweet_count' => 4, 'quote_count' => 0, 'bookmark_count' => 8]],
            ]]),
        ]);

        $connector = new XConnector;
        $tokens = $connector->exchangeCode('code', $this->request());
        Http::assertSent(fn ($req) => str_contains($req->url(), 'oauth2/token') && $req->hasHeader('Authorization') && $req['code_verifier'] === 'verifier');

        $identity = $connector->identity($tokens);
        $this->assertSame('123', $identity->providerAccountId);
        $this->assertSame('marcusreid', $identity->handle);
        $this->assertSame('https://img/m_400x400.jpg', $identity->avatarUrl);

        $contents = collect([
            new SocialContent(['provider_content_id' => 'new', 'content_type' => ContentType::Post, 'published_at' => now()->subDays(3)]),
            new SocialContent(['provider_content_id' => 'old', 'content_type' => ContentType::Post, 'published_at' => now()->subDays(60)]),
        ]);
        $result = $connector->syncMetrics(new AccountContext('123', 'marcusreid', $tokens), $contents);
        $byId = collect($result->contentMetrics)->keyBy('providerContentId');

        $this->assertSame(64000, $byId['new']->metrics['views']);
        $this->assertSame(45, $byId['new']->metrics['reposts']); // retweets + quotes
        $this->assertSame(40, $byId['new']->metrics['profile_clicks']);
        $this->assertArrayNotHasKey('profile_clicks', $byId['old']->metrics); // older than 30 days: public only
        $this->assertTrue($result->audience->isEmpty());

        Http::assertSent(fn ($req) => str_contains($req->url(), 'non_public_metrics') && str_contains($req->url(), 'ids=new') && ! str_contains($req->url(), 'old'));
    }

    public function test_google_login_reads_the_openid_profile(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'at']),
            'openidconnect.googleapis.com/v1/userinfo' => Http::response(['sub' => 'g-1', 'email' => 'John@Example.com', 'email_verified' => true, 'name' => 'John Smith', 'picture' => 'https://img/g.jpg']),
        ]);

        $provider = new LiveGoogleLoginProvider;
        $this->assertStringContainsString('scope=openid+email+profile', $provider->authorizationUrl($this->request()));

        $identity = $provider->identity('code', $this->request());
        $this->assertSame('g-1', $identity->id);
        $this->assertSame('john@example.com', $identity->email);
        $this->assertTrue($identity->emailVerified);
    }

    private function context(string $id, string $handle): AccountContext
    {
        return new AccountContext($id, $handle, new OAuthTokens('at'));
    }
}
