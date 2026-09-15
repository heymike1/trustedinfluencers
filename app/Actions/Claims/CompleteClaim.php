<?php

namespace App\Actions\Claims;

use App\Actions\Sync\ConnectAccount;
use App\Enums\ClaimStatus;
use App\Models\CreatorClaim;
use App\Models\User;
use App\Social\Data\OAuthTokens;
use App\Social\Data\ProviderIdentity;
use Illuminate\Support\Facades\DB;

/**
 * Finishes a claim once the OAuth callback has produced an identity and tokens.
 */
class CompleteClaim
{
    public function __construct(
        private readonly VerifyAccountOwnership $verify,
        private readonly ConnectAccount $connect,
    ) {}

    /**
     * @throws OwnershipMismatchException
     */
    public function handle(CreatorClaim $claim, User $user, ProviderIdentity $identity, OAuthTokens $tokens): CreatorClaim
    {
        $creator = $claim->creator;
        $account = $claim->socialAccount;

        $claim->forceFill([
            'returned_provider_account_id' => $identity->providerAccountId,
            'returned_handle' => $identity->handle,
        ]);

        if ($claim->status !== ClaimStatus::Pending || $claim->user_id !== $user->id) {
            $claim->forceFill(['status' => ClaimStatus::Failed, 'failure_reason' => 'Claim is no longer pending.'])->save();

            throw new OwnershipMismatchException('This claim has expired. Start again from the profile.');
        }

        if ($creator->isClaimed()) {
            $claim->forceFill(['status' => ClaimStatus::Failed, 'failure_reason' => 'Profile already claimed.'])->save();

            throw new OwnershipMismatchException('Someone else claimed this profile while you were signing in.');
        }

        try {
            $this->verify->handle($account, $identity);
        } catch (OwnershipMismatchException $e) {
            $claim->forceFill(['status' => ClaimStatus::Failed, 'failure_reason' => $e->getMessage()])->save();

            throw $e;
        }

        return DB::transaction(function () use ($claim, $creator, $account, $user, $identity, $tokens) {
            $creator->forceFill([
                'user_id' => $user->id,
                'claimed_at' => now(),
            ])->save();

            $this->connect->handle($account, $identity, $tokens);

            $claim->forceFill([
                'status' => ClaimStatus::Verified,
                'verified_at' => now(),
                'failure_reason' => null,
            ])->save();

            return $claim;
        });
    }
}
