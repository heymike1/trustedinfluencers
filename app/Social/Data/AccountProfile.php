<?php

namespace App\Social\Data;

/**
 * Profile-level data for an account. Returned by both public lookups and authenticated syncs;
 * whether it counts as "public" or "verified" is decided by the caller, not the shape.
 */
final class AccountProfile
{
    public function __construct(
        public readonly string $providerAccountId,
        public readonly string $handle,
        public readonly ?string $displayName = null,
        public readonly ?string $avatarUrl = null,
        public readonly ?int $followerCount = null,
        public readonly ?string $bio = null,
        public readonly ?string $website = null,
        /** Account-level metrics, e.g. total views, media count. */
        public readonly array $metrics = [],
        public readonly array $raw = [],
    ) {}
}
