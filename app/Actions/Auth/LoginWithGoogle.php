<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Social\Login\GoogleIdentity;
use Illuminate\Support\Facades\Auth;

/**
 * Finds or creates the user for a Google identity and signs them in.
 *
 * Matching order: the Google account id, then an existing email/password user with the same
 * verified email (so people who signed up by email can switch to Google without a second account).
 */
class LoginWithGoogle
{
    public function handle(GoogleIdentity $identity): User
    {
        $user = User::where('google_id', $identity->id)->first();

        if (! $user && $identity->emailVerified) {
            $user = User::where('email', $identity->email)->first();
        }

        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?? $identity->id,
                'avatar_url' => $user->avatar_url ?? $identity->avatarUrl,
                'email_verified_at' => $user->email_verified_at ?? ($identity->emailVerified ? now() : null),
            ])->save();
        } else {
            $user = User::create([
                'name' => $identity->name,
                'email' => $identity->email,
                'google_id' => $identity->id,
                'avatar_url' => $identity->avatarUrl,
                'password' => null,
            ]);
            $user->forceFill(['email_verified_at' => $identity->emailVerified ? now() : null])->save();
        }

        Auth::login($user, remember: true);

        return $user;
    }
}
