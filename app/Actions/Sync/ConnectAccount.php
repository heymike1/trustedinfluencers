<?php

namespace App\Actions\Sync;

use App\Enums\ConnectionStatus;
use App\Models\CreatorSocialAccount;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;
use App\Social\OAuth\TokenManager;

/**
 * Stores credentials for an account whose ownership has just been verified and queues the import.
 */
class ConnectAccount
{
    public function __construct(
        private readonly TokenManager $tokens,
        private readonly StartAccountSync $startSync,
    ) {}

    public function handle(CreatorSocialAccount $account, ProviderIdentity $identity, OAuthTokens $tokens): CreatorSocialAccount
    {
        $account->forceFill([
            'provider_account_id' => $identity->providerAccountId,
            'handle' => strtolower($identity->handle),
            'profile_url' => $account->platform->profileUrl(strtolower($identity->handle)),
            'display_name' => $identity->displayName ?? $account->display_name,
            'avatar_url' => $identity->avatarUrl ?? $account->avatar_url,
            'follower_count' => $identity->followerCount ?? $account->follower_count,
            'connection_status' => ConnectionStatus::Importing,
            'connected_at' => now(),
            'disconnected_at' => null,
            'last_sync_error' => null,
        ]);

        $this->tokens->store($account, $tokens);
        $this->startSync->handle($account);

        return $account;
    }
}
