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
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;

/**
 * Google OAuth 2.0 + YouTube Data API v3 + YouTube Analytics API v2.
 *
 * Notes from the current API docs:
 *  - Per-video analytics come from reports.query with dimensions=video and filters=video==id1,id2,…
 *  - Impressions and thumbnail click-through rate are NOT exposed by the Analytics API, so we never
 *    show them for YouTube.
 *  - Shorts are not flagged by the API; we classify vertical videos up to 3 minutes as Shorts.
 */
class YouTubeConnector extends AbstractHttpConnector
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const DATA_API = 'https://www.googleapis.com/youtube/v3';

    private const ANALYTICS_API = 'https://youtubeanalytics.googleapis.com/v2/reports';

    private const SCOPES = [
        'https://www.googleapis.com/auth/youtube.readonly',
        'https://www.googleapis.com/auth/yt-analytics.readonly',
    ];

    private const SHORT_MAX_SECONDS = 180;

    public function platform(): Platform
    {
        return Platform::YouTube;
    }

    public function authorizationUrl(OAuthRequest $request): string
    {
        return self::AUTH_URL.'?'.http_build_query(array_filter([
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $request->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $request->state,
            'code_challenge' => $request->codeChallenge(),
            'code_challenge_method' => 'S256',
        ]));
    }

    public function exchangeCode(string $code, OAuthRequest $request): OAuthTokens
    {
        $response = $this->http()->asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'code' => $code,
            'code_verifier' => $request->codeVerifier,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $request->redirectUri,
        ]);

        if ($response->failed()) {
            throw new OAuthException('Google rejected the authorization code: '.($response->json('error_description') ?? $response->status()));
        }

        return $this->tokensFromResponse($response->json());
    }

    public function refreshTokens(OAuthTokens $tokens): OAuthTokens
    {
        if ($tokens->refreshToken === null) {
            throw new ReconnectionRequiredException('No refresh token stored for this YouTube account.');
        }

        $response = $this->http()->asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'refresh_token' => $tokens->refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new ReconnectionRequiredException('Google refused to refresh the token: '.($response->json('error_description') ?? $response->status()));
        }

        return $this->tokensFromResponse($response->json(), fallbackRefresh: $tokens->refreshToken);
    }

    public function identity(OAuthTokens $tokens): ProviderIdentity
    {
        $channel = $this->ownChannel($tokens);

        return new ProviderIdentity(
            providerAccountId: $channel['id'],
            handle: $this->handleFromChannel($channel),
            displayName: $channel['snippet']['title'] ?? null,
            avatarUrl: $channel['snippet']['thumbnails']['medium']['url'] ?? $channel['snippet']['thumbnails']['default']['url'] ?? null,
            followerCount: $this->int($channel['statistics']['subscriberCount'] ?? null),
            raw: $channel,
        );
    }

    public function resolvePublicAccountId(string $handle): ?string
    {
        return $this->publicChannel($handle)['id'] ?? null;
    }

    public function publicProfile(string $handle): ?AccountProfile
    {
        $channel = $this->publicChannel($handle);

        return $channel ? $this->profileFromChannel($channel) : null;
    }

    public function syncProfile(AccountContext $account): AccountProfile
    {
        return $this->profileFromChannel($this->ownChannel($account->tokens));
    }

    public function syncContent(AccountContext $account, int $limit): array
    {
        $channel = $this->ownChannel($account->tokens);
        $uploads = $channel['contentDetails']['relatedPlaylists']['uploads'] ?? null;

        if (! $uploads) {
            return [];
        }

        $videoIds = [];
        $pageToken = null;

        do {
            $response = $this->ensureOk($this->http()->withToken($account->tokens->accessToken)->get(self::DATA_API.'/playlistItems', array_filter([
                'part' => 'contentDetails',
                'playlistId' => $uploads,
                'maxResults' => min(50, $limit - count($videoIds)),
                'pageToken' => $pageToken,
            ])), 'listing uploads');

            foreach ($response->json('items', []) as $item) {
                $videoIds[] = $item['contentDetails']['videoId'];
            }

            $pageToken = $response->json('nextPageToken');
        } while ($pageToken && count($videoIds) < $limit);

        $items = [];

        foreach (array_chunk($videoIds, 50) as $chunk) {
            $response = $this->ensureOk($this->http()->withToken($account->tokens->accessToken)->get(self::DATA_API.'/videos', [
                'part' => 'snippet,contentDetails,statistics,status',
                'id' => implode(',', $chunk),
                'maxResults' => 50,
            ]), 'fetching videos');

            foreach ($response->json('items', []) as $video) {
                if (($video['status']['privacyStatus'] ?? 'public') !== 'public') {
                    continue;
                }

                $duration = $this->durationToSeconds($video['contentDetails']['duration'] ?? null);

                $items[] = new ContentItem(
                    providerContentId: $video['id'],
                    contentType: $this->classify($video, $duration),
                    title: $video['snippet']['title'] ?? null,
                    url: 'https://www.youtube.com/watch?v='.$video['id'],
                    thumbnailUrl: $video['snippet']['thumbnails']['medium']['url'] ?? null,
                    publishedAt: isset($video['snippet']['publishedAt']) ? CarbonImmutable::parse($video['snippet']['publishedAt']) : null,
                    durationSeconds: $duration,
                    metrics: array_filter([
                        'views' => $this->int($video['statistics']['viewCount'] ?? null),
                        'likes' => $this->int($video['statistics']['likeCount'] ?? null),
                        'comments' => $this->int($video['statistics']['commentCount'] ?? null),
                    ], fn ($v) => $v !== null),
                    raw: $video,
                );
            }
        }

        return $items;
    }

    public function syncMetrics(AccountContext $account, Collection $contents): MetricsSyncResult
    {
        $contentMetrics = [];
        $raw = [];

        $earliest = $contents->min('published_at');
        $startDate = $earliest ? CarbonImmutable::instance($earliest)->toDateString() : now()->subYears(2)->toDateString();
        $endDate = now()->toDateString();

        foreach ($contents->pluck('provider_content_id')->chunk(200) as $chunk) {
            $response = $this->ensureOk($this->analytics($account, [
                'ids' => 'channel==MINE',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'dimensions' => 'video',
                'filters' => 'video=='.$chunk->implode(','),
                'metrics' => 'views,likes,comments,shares,estimatedMinutesWatched,averageViewDuration,averageViewPercentage',
                'maxResults' => 200,
                'sort' => '-views',
            ]), 'video analytics');

            foreach ($this->rows($response) as $data) {
                $raw[$data['video']] = $data;

                $contentMetrics[$data['video']] = new ContentMetrics($data['video'], array_filter([
                    'views' => $this->int($data['views'] ?? null),
                    'likes' => $this->int($data['likes'] ?? null),
                    'comments' => $this->int($data['comments'] ?? null),
                    'shares' => $this->int($data['shares'] ?? null),
                    'watch_time_seconds' => isset($data['estimatedMinutesWatched']) ? (int) round($data['estimatedMinutesWatched'] * 60) : null,
                    'average_watch_time_seconds' => $this->float($data['averageViewDuration'] ?? null),
                    'average_view_percentage' => $this->float($data['averageViewPercentage'] ?? null),
                ], fn ($v) => $v !== null), $data);
            }
        }

        // Retention curve and day-by-day views are only available one video at a time.
        foreach ($contents as $content) {
            /** @var SocialContent $content */
            $id = $content->provider_content_id;
            $metrics = $contentMetrics[$id]->metrics ?? [];
            $raw = $contentMetrics[$id]->raw ?? [];
            $insights = [];

            if ($retention = $this->retentionCurve($account, $id)) {
                $insights['retention'] = $retention;
            }

            if ($content->published_at && ($daily = $this->dailyViews($account, $id, CarbonImmutable::instance($content->published_at)))) {
                $insights['daily_views'] = $daily;
                $metrics += [
                    'views_24h' => $daily[0] ?? 0,
                    'views_7d' => array_sum(array_slice($daily, 0, 7)),
                ];
            }

            $contentMetrics[$id] = new ContentMetrics($id, $metrics, $raw, $insights);
        }

        return new MetricsSyncResult(
            accountMetrics: [],
            contentMetrics: array_values($contentMetrics),
            raw: ['videos' => $raw],
            audience: $this->audience($account),
        );
    }

    /**
     * Viewers still watching at each 5% step of the video, as percentages (21 points).
     */
    private function retentionCurve(AccountContext $account, string $videoId): ?array
    {
        $response = $this->analytics($account, [
            'ids' => 'channel==MINE',
            'startDate' => '2000-01-01',
            'endDate' => now()->toDateString(),
            'dimensions' => 'elapsedVideoTimeRatio',
            'filters' => 'video=='.$videoId,
            'metrics' => 'audienceWatchRatio',
        ]);

        if ($response->failed()) {
            return null;
        }

        $points = [];
        foreach ($this->rows($response) as $row) {
            $points[(int) round(((float) $row['elapsedVideoTimeRatio']) * 100)] = (float) $row['audienceWatchRatio'];
        }

        if ($points === []) {
            return null;
        }

        return array_map(function (int $step) use ($points) {
            // The API reports 100 buckets; pick the nearest one for each 5% step.
            $nearest = collect($points)->keys()->sortBy(fn ($k) => abs($k - $step))->first();

            return round(min(100, max(0, $points[$nearest] * 100)), 1);
        }, range(0, 100, 5));
    }

    /**
     * Views per day for the first 30 days after publishing.
     */
    private function dailyViews(AccountContext $account, string $videoId, CarbonImmutable $publishedAt): ?array
    {
        $response = $this->analytics($account, [
            'ids' => 'channel==MINE',
            'startDate' => $publishedAt->toDateString(),
            'endDate' => $publishedAt->addDays(29)->min(CarbonImmutable::now())->toDateString(),
            'dimensions' => 'day',
            'filters' => 'video=='.$videoId,
            'metrics' => 'views',
            'sort' => 'day',
        ]);

        if ($response->failed()) {
            return null;
        }

        return array_map(fn ($row) => (int) $row['views'], $this->rows($response));
    }

    private function audience(AccountContext $account): AudienceInsights
    {
        $since = now()->subDays(90)->toDateString();
        $today = now()->toDateString();

        $demographics = $this->analytics($account, ['ids' => 'channel==MINE', 'startDate' => $since, 'endDate' => $today, 'dimensions' => 'ageGroup,gender', 'metrics' => 'viewerPercentage']);
        $countries = $this->analytics($account, ['ids' => 'channel==MINE', 'startDate' => $since, 'endDate' => $today, 'dimensions' => 'country', 'metrics' => 'views', 'sort' => '-views', 'maxResults' => 10]);
        $devices = $this->analytics($account, ['ids' => 'channel==MINE', 'startDate' => $since, 'endDate' => $today, 'dimensions' => 'deviceType', 'metrics' => 'views']);
        $totals = $this->analytics($account, ['ids' => 'channel==MINE', 'startDate' => now()->subDays(28)->toDateString(), 'endDate' => $today, 'metrics' => 'views,estimatedMinutesWatched,subscribersGained,subscribersLost']);

        $age = [];
        $gender = [];
        foreach ($demographics->successful() ? $this->rows($demographics) : [] as $row) {
            $ageKey = str_replace('age', '', $row['ageGroup']); // "age25-34" → "25-34"
            $age[$ageKey] = ($age[$ageKey] ?? 0) + (float) $row['viewerPercentage'];
            $gender[$row['gender']] = ($gender[$row['gender']] ?? 0) + (float) $row['viewerPercentage'];
        }

        $countryMap = [];
        $countryRows = $countries->successful() ? $this->rows($countries) : [];
        $countryTotal = array_sum(array_column($countryRows, 'views')) ?: 1;
        foreach ($countryRows as $row) {
            $countryMap[$row['country']] = round($row['views'] / $countryTotal * 100, 1);
        }

        $deviceMap = [];
        $deviceRows = $devices->successful() ? $this->rows($devices) : [];
        $deviceTotal = array_sum(array_column($deviceRows, 'views')) ?: 1;
        foreach ($deviceRows as $row) {
            $deviceMap[strtolower($row['deviceType'])] = round($row['views'] / $deviceTotal * 100, 1);
        }

        $totalRow = $totals->successful() ? ($this->rows($totals)[0] ?? []) : [];

        return new AudienceInsights(
            age: array_map(fn ($v) => round($v, 1), $age),
            gender: array_map(fn ($v) => round($v, 1), $gender),
            countries: $countryMap,
            devices: $deviceMap,
            accountMetrics: array_filter([
                'views_28d' => $this->int($totalRow['views'] ?? null),
                'watch_time_seconds_28d' => isset($totalRow['estimatedMinutesWatched']) ? (int) round($totalRow['estimatedMinutesWatched'] * 60) : null,
                'subscribers_gained_28d' => $this->int($totalRow['subscribersGained'] ?? null),
                'subscribers_lost_28d' => $this->int($totalRow['subscribersLost'] ?? null),
            ], fn ($v) => $v !== null),
        );
    }

    private function analytics(AccountContext $account, array $params): Response
    {
        return $this->http()->withToken($account->tokens->accessToken)->get(self::ANALYTICS_API, $params);
    }

    /** @return array<int, array<string, mixed>> rows keyed by column name */
    private function rows(Response $response): array
    {
        $columns = collect($response->json('columnHeaders', []))->pluck('name')->all();

        return array_map(fn ($row) => array_combine($columns, $row), $response->json('rows', []));
    }

    private function ownChannel(OAuthTokens $tokens): array
    {
        $response = $this->ensureOk($this->http()->withToken($tokens->accessToken)->get(self::DATA_API.'/channels', [
            'part' => 'snippet,statistics,contentDetails',
            'mine' => 'true',
        ]), 'loading channel');

        $channel = $response->json('items.0');

        if (! $channel) {
            throw new ReconnectionRequiredException('The Google account that signed in has no YouTube channel.');
        }

        return $channel;
    }

    private function publicChannel(string $handle): ?array
    {
        $apiKey = $this->config('api_key');

        if (! $apiKey) {
            return null;
        }

        $params = str_starts_with(strtoupper($handle), 'UC') && strlen($handle) >= 22
            ? ['id' => $handle]
            : ['forHandle' => '@'.$handle];

        $response = $this->http()->get(self::DATA_API.'/channels', $params + [
            'part' => 'snippet,statistics',
            'key' => $apiKey,
        ]);

        return $response->successful() ? $response->json('items.0') : null;
    }

    private function profileFromChannel(array $channel): AccountProfile
    {
        return new AccountProfile(
            providerAccountId: $channel['id'],
            handle: $this->handleFromChannel($channel),
            displayName: $channel['snippet']['title'] ?? null,
            avatarUrl: $channel['snippet']['thumbnails']['medium']['url'] ?? null,
            followerCount: $this->int($channel['statistics']['subscriberCount'] ?? null),
            bio: $channel['snippet']['description'] ?? null,
            metrics: array_filter([
                'total_views' => $this->int($channel['statistics']['viewCount'] ?? null),
                'video_count' => $this->int($channel['statistics']['videoCount'] ?? null),
            ], fn ($v) => $v !== null),
            raw: $channel,
        );
    }

    private function handleFromChannel(array $channel): string
    {
        $custom = $channel['snippet']['customUrl'] ?? null; // e.g. "@johnsmith"

        return strtolower(ltrim($custom ?: $channel['id'], '@'));
    }

    private function classify(array $video, ?int $duration): ContentType
    {
        if ($duration === null || $duration > self::SHORT_MAX_SECONDS) {
            return ContentType::Video;
        }

        $thumb = $video['snippet']['thumbnails']['high'] ?? $video['snippet']['thumbnails']['medium'] ?? null;
        $vertical = $thumb && isset($thumb['width'], $thumb['height']) && $thumb['height'] > $thumb['width'];

        // Thumbnails are letterboxed 16:9 for Shorts too, so fall back to the title hint.
        $tagged = str_contains(strtolower($video['snippet']['title'] ?? ''), '#shorts');

        return ($vertical || $tagged || $duration <= 60) ? ContentType::Short : ContentType::Video;
    }

    /** ISO 8601 duration ("PT1H2M3S") to seconds. */
    private function durationToSeconds(?string $iso): ?int
    {
        if (! $iso) {
            return null;
        }

        try {
            $i = new \DateInterval($iso);
        } catch (\Throwable) {
            return null;
        }

        return $i->d * 86400 + $i->h * 3600 + $i->i * 60 + $i->s;
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
