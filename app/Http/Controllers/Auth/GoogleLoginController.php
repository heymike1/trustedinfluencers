<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginWithGoogle;
use App\Http\Controllers\Controller;
use App\Social\Exceptions\OAuthException;
use App\Social\Login\GoogleLoginProvider;
use App\Social\OAuth\OAuthSession;
use Illuminate\Http\Request;

class GoogleLoginController extends Controller
{
    public function redirect(OAuthSession $session, GoogleLoginProvider $google)
    {
        $request = $session->begin('google', ['intent' => 'login'], redirectUri: route('login.google.callback'));

        return redirect()->away($google->authorizationUrl($request));
    }

    public function callback(Request $request, OAuthSession $session, GoogleLoginProvider $google, LoginWithGoogle $login)
    {
        $pending = $session->complete('google', $request->query('state'));

        if ($pending === null) {
            return redirect()->route('login')->with('error', 'That sign-in took too long or didn\'t come from us. Please try again.');
        }

        if ($request->filled('error') || ! $request->filled('code')) {
            return redirect()->route('login')->with('error', 'Google sign-in was cancelled.');
        }

        try {
            $identity = $google->identity($request->query('code'), $pending['request']);
        } catch (OAuthException $e) {
            report($e);

            return redirect()->route('login')->with('error', 'Google sign-in didn\'t go through. '.$e->getMessage());
        }

        $login->handle($identity);
        $request->session()->regenerate();

        return redirect()->intended(route('account'));
    }
}
