<?php

namespace App\Actions\Creators;

use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Jobs\SyncPublicProfile;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use App\Social\Support\HandleNormalizer;
use Illuminate\Validation\ValidationException;

/**
 * Attaches another (unconnected) platform account to an existing creator.
 */
class AddSocialAccountToCreator
{
    public function __construct(
        private readonly HandleNormalizer $normalizer,
        private readonly FindExistingSocialAccount $findExisting,
    ) {}

    public function handle(Creator $creator, Platform $platform, string $handleOrUrl): CreatorSocialAccount
    {
        $normalized = $this->normalizer->normalize($platform, $handleOrUrl);

        if ($normalized === null) {
            throw ValidationException::withMessages(['handle' => "That doesn't look like a {$platform->label()} handle or profile link."]);
        }

        if ($creator->socialAccounts()->where('platform', $platform)->exists()) {
            throw ValidationException::withMessages(['platform' => "This profile already has a {$platform->label()} account."]);
        }

        if ($existing = $this->findExisting->handle($platform, $normalized)) {
            throw (new DuplicateCreatorException($existing->creator))->withMessage(
                "{$platform->label()} @{$normalized->handle} is already on another profile."
            );
        }

        $account = $creator->socialAccounts()->create([
            'platform' => $platform,
            'handle' => $normalized->handle,
            'profile_url' => $normalized->profileUrl,
            'provider_account_id' => $normalized->providerAccountId,
            'connection_status' => ConnectionStatus::Unconnected,
        ]);

        SyncPublicProfile::dispatch($account);

        return $account;
    }
}
