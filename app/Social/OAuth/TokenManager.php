<?php

namespace App\Social\OAuth;

use App\Actions\Sync\RefreshCreatorSummary;
use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use App\Social\Data\AccountContext;
use App\Social\Data\OAuthTokens;
use App\Social\Exceptions\ReconnectionRequiredException;
use Carbon\CarbonImmutable;

/**
 * Reads tokens off an account, refreshes them when they are about to expire and persists the result.
 */
class TokenManager
{
    public function __construct(private readonly ConnectorManager $connectors) {}

    public function store(CreatorSocialAccount $account, OAuthTokens $tokens): void
    {
        $account->forceFill([
            'access_token' => $tokens->accessToken,
            // Some providers omit the refresh token on subsequent grants; keep the old one.
            'refresh_token' => $tokens->refreshToken ?? $account->refresh_token,
            'token_expires_at' => $tokens->expiresAt,
            'scopes' => $tokens->scopes ?: $account->scopes,
        ])->save();
    }

    /**
     * @throws ReconnectionRequiredException
     */
    public function contextFor(CreatorSocialAccount $account): AccountContext
    {
        if (! $account->isConnected() || $account->access_token === null) {
            throw new ReconnectionRequiredException('The account is not connected.');
        }

        $tokens = $this->tokensFrom($account);

        if ($account->tokenExpiresSoon()) {
            try {
                $tokens = $this->connectors->for($account->platform)->refreshTokens($tokens);
            } catch (ReconnectionRequiredException $e) {
                $this->markNeedsReconnection($account, $e->getMessage());

                throw $e;
            }

            $this->store($account, $tokens);
        }

        return new AccountContext($account->provider_account_id, $account->handle, $tokens);
    }

    public function tokensFrom(CreatorSocialAccount $account): OAuthTokens
    {
        return new OAuthTokens(
            accessToken: $account->access_token,
            refreshToken: $account->refresh_token,
            expiresAt: $account->token_expires_at ? CarbonImmutable::instance($account->token_expires_at) : null,
            scopes: $account->scopes ?? [],
        );
    }

    public function markNeedsReconnection(CreatorSocialAccount $account, ?string $reason = null): void
    {
        $account->forceFill([
            'connection_status' => ConnectionStatus::NeedsReconnection,
            'last_sync_error' => $reason,
        ])->save();

        if ($account->creator) {
            app(RefreshCreatorSummary::class)->handle($account->creator);
        }
    }
}
