<?php

namespace App\Social\Login;

use App\Social\Data\OAuthRequest;
use App\Social\Exceptions\OAuthException;

/**
 * "Continue with Google" for the site login. Identity only; no YouTube scopes.
 */
interface GoogleLoginProvider
{
    public function authorizationUrl(OAuthRequest $request): string;

    /** @throws OAuthException */
    public function identity(string $code, OAuthRequest $request): GoogleIdentity;
}
