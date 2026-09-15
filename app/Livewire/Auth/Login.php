<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = 'login:'.Str::lower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many tries. Give it '.RateLimiter::availableIn($key).' seconds and try again.');

            return;
        }

        $user = User::where('email', Str::lower($this->email))->first();

        if ($user?->usesGoogleOnly()) {
            $this->addError('email', 'This account signs in with Google. Use the Google button above.');

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('email', 'That email and password don\'t match.');

            return;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        return $this->redirect(session()->pull('url.intended', route('account')));
    }

    public function render(): View
    {
        return view('livewire.auth.login')->title('Sign in');
    }
}
