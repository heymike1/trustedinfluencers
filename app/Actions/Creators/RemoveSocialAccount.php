<?php

namespace App\Actions\Creators;

use App\Actions\Sync\DisconnectAccount;
use App\Actions\Sync\RefreshCreatorSummary;
use App\Models\CreatorSocialAccount;
use Illuminate\Support\Facades\DB;

/**
 * Takes a platform off a profile entirely: tokens and verified data go first (the disconnect
 * policy), then the public listing for that platform. A profile with no platforms left is
 * unlisted, because an empty row in the directory helps nobody.
 */
class RemoveSocialAccount
{
    public function __construct(
        private readonly DisconnectAccount $disconnect,
        private readonly RefreshCreatorSummary $refreshSummary,
    ) {}

    /** @return bool whether the profile was unlisted because this was its last platform */
    public function handle(CreatorSocialAccount $account): bool
    {
        $creator = $account->creator;

        return DB::transaction(function () use ($account, $creator) {
            if ($account->isConnected()) {
                $this->disconnect->handle($account);
            }

            $account->delete();

            $this->refreshSummary->handle($creator);

            if ($creator->socialAccounts()->exists() || ! $creator->is_listed) {
                return false;
            }

            $creator->update(['is_listed' => false]);

            return true;
        });
    }
}
