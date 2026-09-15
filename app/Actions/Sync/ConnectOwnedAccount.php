<?php

namespace App\Actions\Sync;

use App\Actions\Claims\OwnershipMismatchException;
use App\Actions\Claims\VerifyAccountOwnership;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;

/**
 * Connects (or reconnects) an account on a profile the user already owns.
 * Ownership of the social account itself is verified exactly like a claim.
 */
class ConnectOwnedAccount
{
    public function __construct(
        private readonly VerifyAccountOwnership $verify,
        private readonly ConnectAccount $connect,
    ) {}

    public function handle(CreatorSocialAccount $account, User $user, ProviderIdentity $identity, OAuthTokens $tokens): CreatorSocialAccount
    {
        if (! $account->creator->isOwnedBy($user)) {
            throw new OwnershipMismatchException('You do not own this creator profile.');
        }

        $this->verify->handle($account, $identity);

        return $this->connect->handle($account, $identity, $tokens);
    }
}
