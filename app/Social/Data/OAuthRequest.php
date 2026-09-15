<?php

namespace App\Social\Data;

/**
 * Everything a connector needs to build an authorization URL and later exchange the code.
 */
final class OAuthRequest
{
    public function __construct(
        public readonly string $state,
        public readonly string $codeVerifier,
        public readonly string $redirectUri,
        /** Optional handle hint shown by providers that support login_hint (and by the fake screen). */
        public readonly ?string $loginHint = null,
    ) {}

    public function codeChallenge(): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $this->codeVerifier, true)), '+/', '-_'), '=');
    }
}
