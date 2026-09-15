<?php

namespace App\Actions\Claims;

use App\Models\CreatorSocialAccount;
use App\Social\ConnectorManager;
use App\Social\Data\ProviderIdentity;

/**
 * The core trust rule: the account that just authenticated through the platform's official OAuth
 * flow must be the same account attached to the creator profile.
 *
 * Comparison uses the provider's canonical account id whenever we have (or can publicly resolve)
 * one. Only when the platform offers no public lookup do we fall back to the provider-returned
 * handle, which is still authoritative because it comes from the authenticated API, not the user.
 */
class VerifyAccountOwnership
{
    public function __construct(private readonly ConnectorManager $connectors) {}

    /**
     * @return string the provider account id that was matched
     *
     * @throws OwnershipMismatchException
     */
    public function handle(CreatorSocialAccount $account, ProviderIdentity $identity): string
    {
        $expectedId = $account->provider_account_id
            ?? $this->connectors->for($account->platform)->resolvePublicAccountId($account->handle);

        if ($expectedId !== null) {
            if (! hash_equals((string) $expectedId, (string) $identity->providerAccountId)) {
                throw new OwnershipMismatchException(sprintf(
                    'You signed in to %s as @%s, but this profile is for @%s.',
                    $account->platform->label(),
                    $identity->handle,
                    $account->handle,
                ));
            }

            return $identity->providerAccountId;
        }

        if (strtolower($identity->handle) !== strtolower($account->handle)) {
            throw new OwnershipMismatchException(sprintf(
                'You signed in to %s as @%s, but this profile is for @%s.',
                $account->platform->label(),
                $identity->handle,
                $account->handle,
            ));
        }

        return $identity->providerAccountId;
    }
}
