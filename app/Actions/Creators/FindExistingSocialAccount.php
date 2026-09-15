<?php

namespace App\Actions\Creators;

use App\Enums\Platform;
use App\Models\CreatorSocialAccount;
use App\Social\Data\NormalizedHandle;

/**
 * Looks up an account by its canonical identity: provider id first, normalised handle second.
 */
class FindExistingSocialAccount
{
    public function handle(Platform $platform, NormalizedHandle $normalized): ?CreatorSocialAccount
    {
        $query = CreatorSocialAccount::query()->where('platform', $platform);

        if ($normalized->providerAccountId) {
            $byId = (clone $query)->where('provider_account_id', $normalized->providerAccountId)->first();

            if ($byId) {
                return $byId;
            }
        }

        return $query->where('handle', $normalized->handle)->first();
    }
}
