<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_reset_link_is_emailed_to_a_known_address(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPassword::class)->set('email', $user->email)->call('send')->assertSet('sent', true)->assertSee('Check your inbox');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_an_unknown_address_gets_the_same_response_and_no_email(): void
    {
        Notification::fake();

        Livewire::test(ForgotPassword::class)->set('email', 'nobody@example.com')->call('send')->assertSet('sent', true);

        Notification::assertNothingSent();
    }

    public function test_a_valid_token_sets_the_new_password_and_signs_in(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk()->assertSee('Choose a new password');

        Livewire::test(ResetPassword::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_bad_token_is_refused(): void
    {
        $user = User::factory()->create(['password' => 'old-password-1']);

        Livewire::test(ResetPassword::class, ['token' => 'nope'])
            ->set('email', $user->email)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('save')
            ->assertHasErrors('email');

        $this->assertTrue(Hash::check('old-password-1', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_the_login_page_links_to_the_reset_flow(): void
    {
        $this->get(route('login'))->assertSee(route('password.request'));
        $this->get(route('password.request'))->assertOk()->assertSee('Send reset link');
    }
}
