<?php

namespace App\Social\OAuth;

use App\Enums\Platform;
use App\Social\Data\OAuthRequest;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

/**
 * Stores the pending OAuth flow (state, PKCE verifier and what we intend to do with the result)
 * in the user's session so the callback can pick it up and reject forged requests.
 */
class OAuthSession
{
    private const KEY = 'social_oauth';

    public function __construct(private readonly Session $session) {}

    /**
     * @param  array{intent: string, claim_id?: int, account_id?: int}  $payload
     */
    /**
     * @param  Platform|string  $provider  a platform, or 'google' for the site login
     */
    public function begin(Platform|string $provider, array $payload, ?string $loginHint = null, ?string $redirectUri = null): OAuthRequest
    {
        $key = $provider instanceof Platform ? $provider->value : $provider;

        $request = new OAuthRequest(
            state: Str::random(40),
            codeVerifier: Str::random(96),
            redirectUri: $redirectUri ?? route('oauth.callback', ['platform' => $key]),
            loginHint: $loginHint,
        );

        $this->session->put(self::KEY, [
            'platform' => $key,
            'state' => $request->state,
            'code_verifier' => $request->codeVerifier,
            'redirect_uri' => $request->redirectUri,
            'payload' => $payload,
            'started_at' => now()->timestamp,
        ]);

        return $request;
    }

    /**
     * Retrieve and clear the pending flow, validating platform and state.
     *
     * @return array{request: OAuthRequest, payload: array}|null
     */
    public function complete(Platform|string $provider, ?string $state): ?array
    {
        $key = $provider instanceof Platform ? $provider->value : $provider;
        $stored = $this->session->pull(self::KEY);

        if (! is_array($stored) || $state === null) {
            return null;
        }

        if ($stored['platform'] !== $key || ! hash_equals($stored['state'], $state)) {
            return null;
        }

        if (now()->timestamp - $stored['started_at'] > 900) {
            return null;
        }

        return [
            'request' => new OAuthRequest($stored['state'], $stored['code_verifier'], $stored['redirect_uri']),
            'payload' => $stored['payload'],
        ];
    }
}
