<?php

namespace App\Social\Login;

use App\Social\Data\OAuthRequest;
use App\Social\Exceptions\OAuthException;
use Illuminate\Support\Facades\Http;

class LiveGoogleLoginProvider implements GoogleLoginProvider
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public function authorizationUrl(OAuthRequest $request): string
    {
        return self::AUTH_URL.'?'.http_build_query(array_filter([
            'client_id' => config('social.google_login.client_id'),
            'redirect_uri' => $request->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $request->state,
            'code_challenge' => $request->codeChallenge(),
            'code_challenge_method' => 'S256',
            'login_hint' => $request->loginHint,
        ]));
    }

    public function identity(string $code, OAuthRequest $request): GoogleIdentity
    {
        $token = Http::acceptJson()->asForm()->post(self::TOKEN_URL, [
            'client_id' => config('social.google_login.client_id'),
            'client_secret' => config('social.google_login.client_secret'),
            'code' => $code,
            'code_verifier' => $request->codeVerifier,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $request->redirectUri,
        ]);

        if ($token->failed()) {
            throw new OAuthException('Google rejected the sign-in: '.($token->json('error_description') ?? $token->status()));
        }

        $info = Http::acceptJson()->withToken($token->json('access_token'))->get(self::USERINFO_URL);

        if ($info->failed() || ! $info->json('sub')) {
            throw new OAuthException('Could not read the Google account details.');
        }

        return new GoogleIdentity(
            id: $info->json('sub'),
            email: strtolower($info->json('email')),
            name: $info->json('name') ?: strtolower($info->json('email')),
            avatarUrl: $info->json('picture'),
            emailVerified: (bool) $info->json('email_verified', false),
        );
    }
}
