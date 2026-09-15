<?php

namespace App\Social\Login;

use App\Social\Data\OAuthRequest;
use App\Social\Exceptions\OAuthException;

/**
 * Local stand-in: a screen where you type the Google account you want to "sign in" as.
 */
class FakeGoogleLoginProvider implements GoogleLoginProvider
{
    public function authorizationUrl(OAuthRequest $request): string
    {
        return route('oauth.fake.google', array_filter(['state' => $request->state, 'hint' => $request->loginHint]));
    }

    public function identity(string $code, OAuthRequest $request): GoogleIdentity
    {
        $data = str_starts_with($code, 'fake-google:') ? json_decode(base64_decode(substr($code, 12)), true) : null;

        if (! is_array($data) || empty($data['email'])) {
            throw new OAuthException('Invalid fake Google code.');
        }

        $email = strtolower(trim($data['email']));

        return new GoogleIdentity(
            id: 'fake-google-'.sha1($email),
            email: $email,
            name: trim($data['name'] ?? '') ?: ucwords(str_replace(['.', '_'], ' ', strstr($email, '@', true))),
            avatarUrl: 'https://i.pravatar.cc/200?u=google-'.$email,
        );
    }

    public static function codeFor(string $email, string $name): string
    {
        return 'fake-google:'.base64_encode(json_encode(['email' => $email, 'name' => $name]));
    }
}
