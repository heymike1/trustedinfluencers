<?php

namespace App\Social\Connectors;

use App\Enums\ContentType;
use App\Enums\Platform;
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
 * X API v2 with OAuth 2.0 Authorization Code + PKCE (confidential client).
 *
 * Notes from the current API docs:
 *  - Scopes: tweet.read users.read offline.access (offline.access yields a refresh token).
 *  - public_metrics (likes, replies, reposts, quotes, bookmarks, impressions) are available on any post.
 *  - non_public_metrics (engagements, user_profile_clicks, url_link_clicks) require user context and
 *    are only available for posts created within the last 30 days. Older posts keep public metrics only.
 *  - "views" for X are impressions; we store both keys so cross-platform medians work.
 */
class XConnector extends AbstractHttpConnector
{
    private const AUTH_URL = 'https://x.com/i/oauth2/authorize';

    private const TOKEN_URL = 'https://api.x.com/2/oauth2/token';

    private const API = 'https://api.x.com/2';

    private const SCOPES = ['tweet.read', 'users.read', 'offline.access'];

    private const USER_FIELDS = 'id,username,name,profile_image_url,description,url,public_metrics';

    public function platform(): Platform
    {
        return Platform::X;
    }

    public function authorizationUrl(OAuthRequest $request): string
    {
        return self::AUTH_URL.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $request->redirectUri,
            'scope' => implode(' ', self::SCOPES),
            'state' => $request->state,
            'code_challenge' => $request->codeChallenge(),
            'code_challenge_method' => 'S256',
        ]);
    }

    public function exchangeCode(string $code, OAuthRequest $request): OAuthTokens
    {
        $response = $this->http()
            ->withBasicAuth($this->config('client_id'), $this->config('client_secret'))
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $request->redirectUri,
                'code_verifier' => $request->codeVerifier,
                'client_id' => $this->config('client_id'),
            ]);

        if ($response->failed()) {
            throw new OAuthException('X rejected the authorization code: '.($response->json('error_description') ?? $response->status()));
        }

        return $this->tokensFromResponse($response->json());
    }

    public function refreshTokens(OAuthTokens $tokens): OAuthTokens
    {
        if ($tokens->refreshToken === null) {
            throw new ReconnectionRequiredException('No refresh token stored for this X account.');
        }

        $response = $this->http()
            ->withBasicAuth($this->config('client_id'), $this->config('client_secret'))
            ->asForm()
            ->post(self::TOKEN_URL, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokens->refreshToken,
                'client_id' => $this->config('client_id'),
            ]);

        if ($response->failed()) {
            throw new ReconnectionRequiredException('X refused to refresh the token: '.($response->json('error_description') ?? $response->status()));
        }

        return $this->tokensFromResponse($response->json(), fallbackRefresh: $tokens->refreshToken);
    }

    public function identity(OAuthTokens $tokens): ProviderIdentity
    {
        $user = $this->me($tokens);

        return new ProviderIdentity(
            providerAccountId: (string) $user['id'],
            handle: strtolower($user['username']),
            displayName: $user['name'] ?? null,
            avatarUrl: isset($user['profile_image_url']) ? str_replace('_normal', '_400x400', $user['profile_image_url']) : null,
            followerCount: $this->int($user['public_metrics']['followers_count'] ?? null),
            raw: $user,
        );
    }

    public function resolvePublicAccountId(string $handle): ?string
    {
        return $this->publicUser($handle)['id'] ?? null;
    }

    public function publicProfile(string $handle): ?AccountProfile
    {
        $user = $this->publicUser($handle);

        return $user ? $this->profileFromUser($user) : null;
    }

    public function syncProfile(AccountContext $account): AccountProfile
    {
        return $this->profileFromUser($this->me($account->tokens));
    }

    public function syncContent(AccountContext $account, int $limit): array
    {
        $items = [];
        $pagination = null;

        do {
            $response = $this->ensureOk($this->http()->withToken($account->tokens->accessToken)->get(self::API.'/users/'.$account->providerAccountId.'/tweets', array_filter([
                'max_results' => max(5, min(100, $limit - count($items))),
                'exclude' => 'retweets,replies',
                'tweet.fields' => 'id,text,created_at,public_metrics,attachments',
                'expansions' => 'attachments.media_keys',
                'media.fields' => 'media_key,type,preview_image_url,url',
                'pagination_token' => $pagination,
            ])), 'listing posts');

            $media = collect($response->json('includes.media', []))->keyBy('media_key');

            foreach ($response->json('data', []) as $post) {
                $firstMedia = collect($post['attachments']['media_keys'] ?? [])->map(fn ($k) => $media->get($k))->filter()->first();
                $public = $post['public_metrics'] ?? [];

                $items[] = new ContentItem(
                    providerContentId: (string) $post['id'],
                    contentType: ContentType::Post,
                    title: mb_substr($post['text'] ?? '', 0, 200),
                    url: 'https://x.com/'.$account->handle.'/status/'.$post['id'],
                    thumbnailUrl: $firstMedia['preview_image_url'] ?? $firstMedia['url'] ?? null,
                    publishedAt: isset($post['created_at']) ? CarbonImmutable::parse($post['created_at']) : null,
                    metrics: $this->publicMetrics($public),
                    raw: $post,
                );
            }

            $pagination = $response->json('meta.next_token');
        } while ($pagination && count($items) < $limit);

        return $items;
    }

    public function syncMetrics(AccountContext $account, Collection $contents): MetricsSyncResult
    {
        $contentMetrics = [];
        $cutoff = now()->subDays(29);

        // Public metrics for everything, refreshed in one lookup per 100 ids.
        foreach ($contents->pluck('provider_content_id')->chunk(100) as $chunk) {
            $response = $this->ensureOk($this->http()->withToken($account->tokens->accessToken)->get(self::API.'/tweets', [
                'ids' => $chunk->implode(','),
                'tweet.fields' => 'id,created_at,public_metrics',
            ]), 'loading post metrics');

            foreach ($response->json('data', []) as $post) {
                $contentMetrics[$post['id']] = new ContentMetrics((string) $post['id'], $this->publicMetrics($post['public_metrics'] ?? []), $post);
            }
        }

        // Private metrics only exist for the last 30 days; request them separately so older posts don't error.
        $recentIds = $contents->filter(fn ($c) => $c->published_at && $c->published_at->gt($cutoff))->pluck('provider_content_id');

        foreach ($recentIds->chunk(100) as $chunk) {
            $response = $this->http()->withToken($account->tokens->accessToken)->get(self::API.'/tweets', [
                'ids' => $chunk->implode(','),
                'tweet.fields' => 'id,non_public_metrics,organic_metrics',
            ]);

            if ($response->failed()) {
                $this->ensureOk($response, 'loading private post metrics');
            }

            foreach ($response->json('data', []) as $post) {
                $private = $post['non_public_metrics'] ?? [];
                $organic = $post['organic_metrics'] ?? [];
                $existing = $contentMetrics[$post['id']] ?? new ContentMetrics((string) $post['id'], []);

                $contentMetrics[$post['id']] = new ContentMetrics((string) $post['id'], array_filter($existing->metrics + [
                    'engagements' => $this->int($private['engagements'] ?? null),
                    'profile_clicks' => $this->int($private['user_profile_clicks'] ?? null),
                    'url_clicks' => $this->int($private['url_link_clicks'] ?? null),
                    'organic_impressions' => $this->int($organic['impression_count'] ?? null),
                ], fn ($v) => $v !== null), $existing->raw + ['non_public_metrics' => $private, 'organic_metrics' => $organic]);
            }
        }

        // X exposes no audience demographics through the API; only per-post metrics are available.
        return new MetricsSyncResult(accountMetrics: [], contentMetrics: array_values($contentMetrics), audience: new AudienceInsights);
    }

    private function publicMetrics(array $public): array
    {
        $impressions = $this->int($public['impression_count'] ?? null);

        return array_filter([
            'impressions' => $impressions,
            'views' => $impressions,
            'likes' => $this->int($public['like_count'] ?? null),
            'replies' => $this->int($public['reply_count'] ?? null),
            'reposts' => isset($public['retweet_count']) ? (int) $public['retweet_count'] + (int) ($public['quote_count'] ?? 0) : null,
            'bookmarks' => $this->int($public['bookmark_count'] ?? null),
        ], fn ($v) => $v !== null);
    }

    private function me(OAuthTokens $tokens): array
    {
        $response = $this->ensureOk($this->http()->withToken($tokens->accessToken)->get(self::API.'/users/me', [
            'user.fields' => self::USER_FIELDS,
        ]), 'loading profile');

        return $response->json('data');
    }

    private function publicUser(string $handle): ?array
    {
        $bearer = $this->config('bearer_token');

        if (! $bearer) {
            return null;
        }

        $response = $this->http()->withToken($bearer)->get(self::API.'/users/by/username/'.$handle, [
            'user.fields' => self::USER_FIELDS,
        ]);

        return $response->successful() ? $response->json('data') : null;
    }

    private function profileFromUser(array $user): AccountProfile
    {
        $metrics = $user['public_metrics'] ?? [];

        return new AccountProfile(
            providerAccountId: (string) $user['id'],
            handle: strtolower($user['username']),
            displayName: $user['name'] ?? null,
            avatarUrl: isset($user['profile_image_url']) ? str_replace('_normal', '_400x400', $user['profile_image_url']) : null,
            followerCount: $this->int($metrics['followers_count'] ?? null),
            bio: $user['description'] ?? null,
            website: $user['url'] ?? null,
            metrics: array_filter([
                'post_count' => $this->int($metrics['tweet_count'] ?? $metrics['post_count'] ?? null),
                'following_count' => $this->int($metrics['following_count'] ?? null),
            ], fn ($v) => $v !== null),
            raw: $user,
        );
    }

    private function tokensFromResponse(array $data, ?string $fallbackRefresh = null): OAuthTokens
    {
        return new OAuthTokens(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? $fallbackRefresh,
            expiresAt: isset($data['expires_in']) ? CarbonImmutable::now()->addSeconds((int) $data['expires_in']) : null,
            scopes: isset($data['scope']) ? explode(' ', $data['scope']) : self::SCOPES,
        );
    }
}
