<?php

namespace App\Actions\Sync;

use App\Models\CreatorSocialAccount;
use App\Models\User;
use App\Social\ConnectorManager;
use App\Social\OAuth\OAuthSession;
use Illuminate\Validation\ValidationException;

/**
 * Begins OAuth for connecting/reconnecting an account on a profile the user owns.
 */
class StartAccountConnection
{
    public function __construct(
        private readonly ConnectorManager $connectors,
        private readonly OAuthSession $oauth,
    ) {}

    public function handle(CreatorSocialAccount $account, User $user): string
    {
        if (! $account->creator->isOwnedBy($user)) {
            throw ValidationException::withMessages(['connect' => 'You do not own this creator profile.']);
        }

        $request = $this->oauth->begin($account->platform, ['intent' => 'connect', 'account_id' => $account->id], $account->handle);

        return $this->connectors->for($account->platform)->authorizationUrl($request);
    }
}
