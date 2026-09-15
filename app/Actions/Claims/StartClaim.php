<?php

namespace App\Actions\Claims;

use App\Enums\ClaimStatus;
use App\Models\Creator;
use App\Models\CreatorClaim;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use App\Social\ConnectorManager;
use App\Social\OAuth\OAuthSession;
use Illuminate\Validation\ValidationException;

/**
 * Records a pending claim and returns the provider's authorization URL.
 */
class StartClaim
{
    public function __construct(
        private readonly ConnectorManager $connectors,
        private readonly OAuthSession $oauth,
    ) {}

    public function handle(Creator $creator, CreatorSocialAccount $account, User $user): string
    {
        if ($creator->isClaimed()) {
            throw ValidationException::withMessages(['claim' => 'This profile has already been claimed.']);
        }

        if ($account->creator_id !== $creator->id) {
            throw ValidationException::withMessages(['claim' => 'That account does not belong to this profile.']);
        }

        if ($user->creator()->exists()) {
            throw ValidationException::withMessages(['claim' => 'Your login already has a creator profile.']);
        }

        $claim = CreatorClaim::create([
            'creator_id' => $creator->id,
            'user_id' => $user->id,
            'creator_social_account_id' => $account->id,
            'platform' => $account->platform,
            'status' => ClaimStatus::Pending,
            'expected_provider_account_id' => $account->provider_account_id,
        ]);

        $request = $this->oauth->begin($account->platform, ['intent' => 'claim', 'claim_id' => $claim->id], $account->handle);

        return $this->connectors->for($account->platform)->authorizationUrl($request);
    }
}
