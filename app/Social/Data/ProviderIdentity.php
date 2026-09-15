<?php

namespace App\Social\Data;

/**
 * Who the provider says just authenticated. Used to verify ownership of a profile.
 */
final class ProviderIdentity
{
    public function __construct(
        public readonly string $providerAccountId,
        public readonly string $handle,
        public readonly ?string $displayName = null,
        public readonly ?string $avatarUrl = null,
        public readonly ?int $followerCount = null,
        public readonly array $raw = [],
    ) {}
}
