<?php

namespace App\Social\Data;

use Carbon\CarbonImmutable;

final class OAuthTokens
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?CarbonImmutable $expiresAt = null,
        public readonly array $scopes = [],
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt->isPast();
    }
}
