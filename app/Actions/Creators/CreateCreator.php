<?php

namespace App\Actions\Creators;

use App\Enums\ConnectionStatus;
use App\Enums\CreatorStatus;
use App\Enums\Platform;
use App\Jobs\SyncPublicProfile;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use App\Social\Support\HandleNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates a public (unclaimed) creator profile with one social account.
 */
class CreateCreator
{
    public function __construct(
        private readonly HandleNormalizer $normalizer,
        private readonly FindExistingSocialAccount $findExisting,
    ) {}

    /**
     * @throws ValidationException when the handle is invalid or the account is already listed.
     */
    public function handle(string $name, Platform $platform, string $handleOrUrl, ?int $categoryId = null): Creator
    {
        $normalized = $this->normalizer->normalize($platform, $handleOrUrl);

        if ($normalized === null) {
            throw ValidationException::withMessages(['handle' => "That doesn't look like a {$platform->label()} handle or profile link."]);
        }

        if ($existing = $this->findExisting->handle($platform, $normalized)) {
            throw (new DuplicateCreatorException($existing->creator))->withMessage(
                "{$platform->label()} @{$normalized->handle} is already listed."
            );
        }

        $creator = DB::transaction(function () use ($name, $platform, $normalized, $categoryId) {
            $creator = Creator::create([
                'name' => trim($name),
                'slug' => Creator::uniqueSlugFor($name),
                'creator_category_id' => $categoryId,
                'status' => CreatorStatus::Active,
            ]);

            $creator->socialAccounts()->create([
                'platform' => $platform,
                'handle' => $normalized->handle,
                'profile_url' => $normalized->profileUrl,
                'provider_account_id' => $normalized->providerAccountId,
                'connection_status' => ConnectionStatus::Unconnected,
            ]);

            return $creator;
        });

        $creator->socialAccounts->each(fn (CreatorSocialAccount $a) => SyncPublicProfile::dispatch($a));

        return $creator;
    }
}
