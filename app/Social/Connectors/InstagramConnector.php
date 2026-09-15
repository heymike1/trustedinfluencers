<?php

namespace App\Social\Connectors;

use App\Enums\ContentType;
use App\Enums\Platform;
use App\Models\SocialContent;
use App\Social\Data\AccountContext;
use App\Social\Data\AccountProfile;
use App\Social\Data\AudienceInsights;
use App\Social\Data\ContentItem;
use App\Social\Data\ContentMetrics;
use App\Social\Data\MetricsSyncResult;
use App\Social\Data\OAuthRequest;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;
use App\Social\Exceptions\OAuthException;
use App\Social\Exceptions\ReconnectionRequiredException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * "Instagram API with Instagram Login" (Business Login), graph.instagram.com.
 *
 * Notes from the current API docs:
 *  - Scopes: instagram_business_basic + instagram_business_manage_insights.
 *  - Short-lived tokens are exchanged for 60-day long-lived tokens which can be refreshed
 *    (at least 24h old, not yet expired). There is no classic refresh token.
 *  - "impressions" and "plays" are deprecated for media created after July 2024; "views" replaces them.
 *  - Media insights differ per product type; Stories are not imported (24h lifetime).
 *  - There is no public lookup by username, so unclaimed profiles show submitted data only.
 */
class InstagramConnector extends AbstractHttpConnector
{
    private const AUTH_URL = 'https://www.instagram.com/oauth/authorize';

    private const TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    private const GRAPH = 'https://graph.instagram.com';

    private const GRAPH_VERSION = 'v23.0';

    private const SCOPES = ['instagram_business_basic', 'instagram_business_manage_insights'];

    private const PROFILE_FIELDS = 'user_id,username,name,profile_picture_url,followers_count,follows_count,media_count,biography,website';

    public function platform(): Platform
    {
        return Platform::Instagram;
    }

    public function authorizationUrl(OAuthRequest $request): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $request->redirectUri,
            'response_type' => 'code',
            'scope' => implode(',', self::SCOPES),
            'state' => $request->state,
        ]);
    }

    public function exchangeCode(string $code, OAuthRequest $request): OAuthTokens
    {
        $short = $this->http()->asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $request->redirectUri,
            'code' => $code,
        ]);

        if ($short->failed() || ! $short->json('access_token')) {
            throw new OAuthException('Instagram rejected the authorization code: '.($short->json('error_message') ?? $short->status()));
        }

        $long = $this->http()->get(self::GRAPH.'/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $this->config('client_secret'),
            'access_token' => $short->json('access_token'),
        ]);

        if ($long->failed()) {
            throw new OAuthException('Could not exchange the Instagram token for a long-lived token.');
        }

        return new OAuthTokens(
            accessToken: $long->json('access_token'),
            refreshToken: null,
            expiresAt: CarbonImmutable::now()->addSeconds((int) $long->json('expires_in', 60 * 86400)),
            scopes: (array) ($short->json('permissions') ?? self::SCOPES),
        );
    }

    public function refreshTokens(OAuthTokens $tokens): OAuthTokens
    {
        $response = $this->http()->get(self::GRAPH.'/refresh_access_token', [
            'grant_type' => 'ig_refresh_token',
            'access_token' => $tokens->accessToken,
        ]);

        if ($response->failed()) {
            throw new ReconnectionRequiredException('Instagram refused to refresh the long-lived token.');
        }

        return new OAuthTokens(
            accessToken: $response->json('access_token'),
            refreshToken: null,
            expiresAt: CarbonImmutable::now()->addSeconds((int) $response->json('expires_in', 60 * 86400)),
            scopes: $tokens->scopes,
        );
    }

    public function identity(OAuthTokens $tokens): ProviderIdentity
    {
        $me = $this->me($tokens);

        return new ProviderIdentity(
            providerAccountId: (string) ($me['user_id'] ?? $me['id']),
            handle: strtolower($me['username']),
            displayName: $me['name'] ?? $me['username'],
            avatarUrl: $me['profile_picture_url'] ?? null,
            followerCount: $this->int($me['followers_count'] ?? null),
            raw: $me,
        );
    }

    public function resolvePublicAccountId(string $handle): ?string
    {
        return null; // No public username lookup exists in this API.
    }

    public function publicProfile(string $handle): ?AccountProfile
    {
        return null;
    }

    public function syncProfile(AccountContext $account): AccountProfile
    {
        $me = $this->me($account->tokens);

        return new AccountProfile(
            providerAccountId: (string) ($me['user_id'] ?? $me['id']),
            handle: strtolower($me['username']),
            displayName: $me['name'] ?? $me['username'],
            avatarUrl: $me['profile_picture_url'] ?? null,
            followerCount: $this->int($me['followers_count'] ?? null),
            bio: $me['biography'] ?? null,
            website: $me['website'] ?? null,
            metrics: array_filter([
                'media_count' => $this->int($me['media_count'] ?? null),
                'following_count' => $this->int($me['follows_count'] ?? null),
            ], fn ($v) => $v !== null),
            raw: $me,
        );
    }

    public function syncContent(AccountContext $account, int $limit): array
    {
        $items = [];
        $url = self::GRAPH.'/'.self::GRAPH_VERSION.'/me/media';
        $params = [
            'fields' => 'id,media_type,media_product_type,caption,permalink,media_url,thumbnail_url,timestamp,like_count,comments_count',
            'limit' => min(50, $limit),
            'access_token' => $account->tokens->accessToken,
        ];

        while ($url && count($items) < $limit) {
            $response = $this->ensureOk($this->http()->get($url, $params), 'listing media');
            $params = [];

            foreach ($response->json('data', []) as $media) {
                $productType = $media['media_product_type'] ?? 'FEED';

                if ($productType === 'STORY') {
                    continue;
                }

                $items[] = new ContentItem(
                    providerContentId: (string) $media['id'],
                    contentType: $productType === 'REELS' ? ContentType::Reel : ContentType::Post,
                    title: isset($media['caption']) ? mb_substr($media['caption'], 0, 200) : null,
                    url: $media['permalink'] ?? null,
                    thumbnailUrl: $media['thumbnail_url'] ?? (($media['media_type'] ?? '') !== 'VIDEO' ? ($media['media_url'] ?? null) : null),
                    publishedAt: isset($media['timestamp']) ? CarbonImmutable::parse($media['timestamp']) : null,
                    metrics: array_filter([
                        'likes' => $this->int($media['like_count'] ?? null),
                        'comments' => $this->int($media['comments_count'] ?? null),
                    ], fn ($v) => $v !== null),
                    raw: $media,
                );

                if (count($items) >= $limit) {
                    break;
                }
            }

            $url = $response->json('paging.next');
        }

        return $items;
    }

    public function syncMetrics(AccountContext $account, Collection $contents): MetricsSyncResult
    {
        $contentMetrics = [];

        /** @var SocialContent $content */
        foreach ($contents as $content) {
            $metricNames = $content->content_type === ContentType::Reel
                ? 'views,reach,likes,comments,shares,saved,total_interactions,ig_reels_avg_watch_time,ig_reels_video_view_total_time'
                : 'views,reach,likes,comments,shares,saved,total_interactions,profile_visits,follows';

            $response = $this->http()->get(self::GRAPH.'/'.self::GRAPH_VERSION.'/'.$content->provider_content_id.'/insights', [
                'metric' => $metricNames,
                'access_token' => $account->tokens->accessToken,
            ]);

            // Individual media can legitimately refuse insights (e.g. too few views). Skip, don't fail.
            if ($response->failed()) {
                if (in_array($response->status(), [401, 403], true) && $response->json('error.code') === 190) {
                    $this->ensureOk($response, 'media insights');
                }

                continue;
            }

            $values = [];
            foreach ($response->json('data', []) as $metric) {
                $values[$metric['name']] = $metric['values'][0]['value'] ?? $metric['total_value']['value'] ?? null;
            }

            $contentMetrics[] = new ContentMetrics((string) $content->provider_content_id, array_filter([
                'views' => $this->int($values['views'] ?? null),
                'reach' => $this->int($values['reach'] ?? null),
                'likes' => $this->int($values['likes'] ?? null),
                'comments' => $this->int($values['comments'] ?? null),
                'shares' => $this->int($values['shares'] ?? null),
                'saves' => $this->int($values['saved'] ?? null),
                'engagements' => $this->int($values['total_interactions'] ?? null),
                'profile_visits' => $this->int($values['profile_visits'] ?? null),
                'follows' => $this->int($values['follows'] ?? null),
                // Reel watch time comes back in milliseconds.
                'average_watch_time_seconds' => isset($values['ig_reels_avg_watch_time']) ? round($values['ig_reels_avg_watch_time'] / 1000, 2) : null,
                'watch_time_seconds' => isset($values['ig_reels_video_view_total_time']) ? (int) round($values['ig_reels_video_view_total_time'] / 1000) : null,
            ], fn ($v) => $v !== null), $values);
        }

        return new MetricsSyncResult(accountMetrics: [], contentMetrics: $contentMetrics, audience: $this->audience($account));
    }

    /**
     * Follower demographics, follower vs non-follower reach and 30-day account totals.
     * Each call can legitimately fail (accounts under 100 followers get no demographics), so
     * failures leave that breakdown empty instead of failing the sync.
     */
    private function audience(AccountContext $account): AudienceInsights
    {
        $token = $account->tokens->accessToken;
        $base = self::GRAPH.'/'.self::GRAPH_VERSION.'/me/insights';

        $breakdown = function (string $name) use ($base, $token): array {
            $response = $this->http()->get($base, [
                'metric' => 'follower_demographics',
                'period' => 'lifetime',
                'metric_type' => 'total_value',
                'breakdown' => $name,
                'access_token' => $token,
            ]);

            $results = $response->successful() ? ($response->json('data.0.total_value.breakdowns.0.results') ?? []) : [];
            $map = [];
            foreach ($results as $row) {
                $map[$row['dimension_values'][0] ?? '?'] = (float) ($row['value'] ?? 0);
            }
            $total = array_sum($map) ?: 1;

            return array_map(fn ($v) => round($v / $total * 100, 1), $map);
        };

        $since = now()->subDays(30)->timestamp;
        $until = now()->timestamp;

        $reach = $this->http()->get($base, [
            'metric' => 'reach',
            'period' => 'day',
            'metric_type' => 'total_value',
            'breakdown' => 'follow_type',
            'since' => $since,
            'until' => $until,
            'access_token' => $token,
        ]);
        $followerType = [];
        foreach ($reach->successful() ? ($reach->json('data.0.total_value.breakdowns.0.results') ?? []) : [] as $row) {
            $key = strtolower($row['dimension_values'][0] ?? '') === 'follower' ? 'follower' : 'non_follower';
            $followerType[$key] = ($followerType[$key] ?? 0) + (float) ($row['value'] ?? 0);
        }
        $reachTotal = array_sum($followerType) ?: 1;

        $totals = $this->http()->get($base, [
            'metric' => 'reach,profile_views,website_clicks',
            'period' => 'day',
            'metric_type' => 'total_value',
            'since' => $since,
            'until' => $until,
            'access_token' => $token,
        ]);
        $accountMetrics = [];
        foreach ($totals->successful() ? $totals->json('data', []) : [] as $metric) {
            $accountMetrics[$metric['name'].'_30d'] = (int) ($metric['total_value']['value'] ?? 0);
        }

        return new AudienceInsights(
            age: $breakdown('age'),
            gender: $breakdown('gender'),
            countries: $breakdown('country'),
            cities: $breakdown('city'),
            followerType: array_map(fn ($v) => round($v / $reachTotal * 100, 1), $followerType),
            accountMetrics: $accountMetrics,
        );
    }

    private function me(OAuthTokens $tokens): array
    {
        $response = $this->ensureOk($this->http()->get(self::GRAPH.'/'.self::GRAPH_VERSION.'/me', [
            'fields' => self::PROFILE_FIELDS,
            'access_token' => $tokens->accessToken,
        ]), 'loading profile');

        return $response->json();
    }
}
