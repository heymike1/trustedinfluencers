<?php

namespace App\Social\Contracts;

use App\Enums\Platform;
use App\Models\SocialContent;
use App\Social\Data\AccountContext;
use App\Social\Data\AccountProfile;
use App\Social\Data\ContentItem;
use App\Social\Data\MetricsSyncResult;
use App\Social\Data\OAuthRequest;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;
use App\Social\Exceptions\ReconnectionRequiredException;
use Illuminate\Support\Collection;

/**
 * One implementation per platform (plus a fake one for local development).
 * Connectors are stateless and never touch Eloquent; jobs and actions do the persistence.
 */
interface SocialPlatformConnector
{
    public function platform(): Platform;

    /** Build the provider's authorization URL for the given state / PKCE pair. */
    public function authorizationUrl(OAuthRequest $request): string;

    /** Exchange the callback code for tokens. */
    public function exchangeCode(string $code, OAuthRequest $request): OAuthTokens;

    /**
     * Obtain fresh tokens.
     *
     * @throws ReconnectionRequiredException when the platform does not support refresh
     *                                       or the refresh token is no longer valid.
     */
    public function refreshTokens(OAuthTokens $tokens): OAuthTokens;

    /** Who authenticated. The canonical provider account id is what ownership is verified against. */
    public function identity(OAuthTokens $tokens): ProviderIdentity;

    /**
     * Resolve a handle to the provider's canonical account id using public endpoints.
     * Returns null when the platform offers no public lookup or credentials are missing.
     */
    public function resolvePublicAccountId(string $handle): ?string;

    /** Publicly available profile data for an unclaimed account, or null when unavailable. */
    public function publicProfile(string $handle): ?AccountProfile;

    /** Profile data for a connected account. */
    public function syncProfile(AccountContext $account): AccountProfile;

    /**
     * Recent content for a connected account, newest first.
     *
     * @return ContentItem[]
     */
    public function syncContent(AccountContext $account, int $limit): array;

    /**
     * Private analytics for the given content items plus account-level metrics.
     *
     * @param  Collection<int, SocialContent>  $contents
     */
    public function syncMetrics(AccountContext $account, Collection $contents): MetricsSyncResult;
}
