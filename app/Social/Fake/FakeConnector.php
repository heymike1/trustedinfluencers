<?php

namespace App\Social\Fake;

use App\Enums\Platform;
use App\Models\SocialContent;
use App\Social\Contracts\SocialPlatformConnector;
use App\Social\Data\AccountContext;
use App\Social\Data\AccountProfile;
use App\Social\Data\MetricsSyncResult;
use App\Social\Data\OAuthRequest;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;
use App\Social\Exceptions\OAuthException;
use App\Social\Exceptions\ReconnectionRequiredException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Simulates a provider end to end: a local "sign in as…" screen replaces the real OAuth consent
 * page, tokens encode which handle authenticated, and data comes from FakeDataGenerator.
 *
 * Nothing here is ever used when social.driver = live.
 */
class FakeConnector implements SocialPlatformConnector
{
    public function __construct(
        private readonly Platform $platform,
        private readonly FakeDataGenerator $generator,
    ) {}

    public function platform(): Platform
    {
        return $this->platform;
    }

    public function authorizationUrl(OAuthRequest $request): string
    {
        return route('oauth.fake.authorize', array_filter([
            'platform' => $this->platform->value,
            'state' => $request->state,
            'hint' => $request->loginHint,
        ]));
    }

    public function exchangeCode(string $code, OAuthRequest $request): OAuthTokens
    {
        $handle = self::handleFromCode($code);

        if ($handle === null) {
            throw new OAuthException('Invalid fake authorization code.');
        }

        return new OAuthTokens(
            accessToken: self::tokenFor($this->platform, $handle),
            refreshToken: 'fake-refresh:'.$handle,
            expiresAt: CarbonImmutable::now()->addHour(),
            scopes: ['fake.read'],
        );
    }

    public function refreshTokens(OAuthTokens $tokens): OAuthTokens
    {
        if (! $tokens->refreshToken || ! str_starts_with($tokens->refreshToken, 'fake-refresh:')) {
            throw new ReconnectionRequiredException('Fake refresh token missing.');
        }

        $handle = substr($tokens->refreshToken, strlen('fake-refresh:'));

        return new OAuthTokens(
            accessToken: self::tokenFor($this->platform, $handle),
            refreshToken: $tokens->refreshToken,
            expiresAt: CarbonImmutable::now()->addHour(),
            scopes: $tokens->scopes,
        );
    }

    public function identity(OAuthTokens $tokens): ProviderIdentity
    {
        return $this->generator->identity($this->platform, $this->handleFromToken($tokens));
    }

    public function resolvePublicAccountId(string $handle): ?string
    {
        // Mirrors the live drivers: Instagram has no public lookup.
        if ($this->platform === Platform::Instagram) {
            return null;
        }

        return $this->generator->providerAccountId($this->platform, $handle);
    }

    public function publicProfile(string $handle): ?AccountProfile
    {
        if ($this->platform === Platform::Instagram) {
            return null;
        }

        return $this->generator->profile($this->platform, $handle, authenticated: false);
    }

    public function syncProfile(AccountContext $account): AccountProfile
    {
        return $this->generator->profile($this->platform, $this->handleFromToken($account->tokens), authenticated: true);
    }

    public function syncContent(AccountContext $account, int $limit): array
    {
        return $this->generator->content($this->platform, $this->handleFromToken($account->tokens), $limit);
    }

    public function syncMetrics(AccountContext $account, Collection $contents): MetricsSyncResult
    {
        $handle = $this->handleFromToken($account->tokens);

        $metrics = $contents->map(fn (SocialContent $content) => $this->generator->metrics(
            $this->platform,
            $handle,
            $content->content_type,
            $content->provider_content_id,
            $content->published_at ? (int) $content->published_at->diffInDays(now()) : 30,
        ))->all();

        return new MetricsSyncResult(accountMetrics: [], contentMetrics: $metrics, raw: ['fake' => true], audience: $this->generator->audience($this->platform, $handle));
    }

    /** The "code" our fake consent screen sends back. */
    public static function codeFor(string $handle): string
    {
        return 'fake:'.base64_encode(strtolower(trim($handle)));
    }

    public static function handleFromCode(string $code): ?string
    {
        if (! str_starts_with($code, 'fake:')) {
            return null;
        }

        $handle = base64_decode(substr($code, 5), true);

        return $handle === false || $handle === '' ? null : $handle;
    }

    public static function tokenFor(Platform $platform, string $handle): string
    {
        return "fake-token:{$platform->value}:{$handle}:".bin2hex(random_bytes(8));
    }

    private function handleFromToken(OAuthTokens $tokens): string
    {
        $parts = explode(':', $tokens->accessToken);

        if (($parts[0] ?? null) !== 'fake-token' || ($parts[1] ?? null) !== $this->platform->value || empty($parts[2])) {
            throw new ReconnectionRequiredException('Fake access token is invalid for this platform.');
        }

        return $parts[2];
    }
}
