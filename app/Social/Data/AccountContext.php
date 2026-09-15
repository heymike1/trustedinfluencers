<?php

namespace App\Social\Data;

/**
 * The minimum a connector needs to call the API on behalf of a connected account.
 */
final class AccountContext
{
    public function __construct(
        public readonly string $providerAccountId,
        public readonly string $handle,
        public readonly OAuthTokens $tokens,
    ) {}
}
