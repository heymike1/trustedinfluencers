<?php

namespace App\Livewire\Account;

use App\Actions\Auth\DeleteUserAccount;
use App\Livewire\Concerns\HasNotice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The login itself, separate from the public profile: name, email address and password.
 */
#[Layout('components.layouts.app', ['band' => true])]
class LoginSettings extends Component
{
    use HasNotice;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = $this->user()->name;
        $this->email = $this->user()->email;
    }

    private function user(): User
    {
        return auth()->user();
    }

    public function saveAccount(): void
    {
        $user = $this->user();

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        $this->notify('success', 'Account details saved.');
    }

    public function savePassword(): void
    {
        $user = $this->user();

        $this->validate([
            'current_password' => [$user->password === null ? 'nullable' : 'required'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($user->password !== null && ! Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'That is not your current password.']);
        }

        $user->password = $this->password;
        $user->save();

        // A password change invalidates other sessions; keep this one signed in.
        Auth::logoutOtherDevices($this->password);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->notify('success', $user->wasChanged('password') ? 'Password updated. Other devices were signed out.' : 'Password set.');
    }

    public function deleteAccount(DeleteUserAccount $delete): void
    {
        try {
            $delete->handle($this->user());
        } catch (ValidationException $e) {
            $this->addError('account', collect($e->errors())->flatten()->first());

            return;
        }

        // Not Auth::logout(): cycling the remember token would save the deleted model back.
        Auth::forgetUser();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('success', 'Your account and profile have been deleted.');

        $this->redirectRoute('home');
    }

    public function render(): View
    {
        return view('livewire.account.login-settings', [
            'user' => $this->user(),
            'creator' => $this->user()->creator,
        ])->title('Login and password');
    }
}
