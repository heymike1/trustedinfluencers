<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use App\Social\Login\FakeGoogleLoginProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_offers_google_and_email(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Continue with Google')->assertSee('or use email');
        $this->get(route('register'))->assertOk()->assertSee('Sign up with Google');
    }

    public function test_starting_google_login_redirects_to_the_provider(): void
    {
        $response = $this->get(route('login.google'));

        $response->assertRedirect();
        $this->assertStringStartsWith(route('oauth.fake.google'), $response->headers->get('Location'));
        $this->assertNotNull(session('social_oauth.state'));
    }

    public function test_a_new_google_user_is_created_and_signed_in(): void
    {
        $this->get(route('login.google'));
        $state = session('social_oauth.state');

        $this->get(route('login.google.callback', ['state' => $state, 'code' => FakeGoogleLoginProvider::codeFor('new@example.com', 'New Person')]))
            ->assertRedirect(route('account'));

        $user = User::where('email', 'new@example.com')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('New Person', $user->name);
        $this->assertNull($user->password);
        $this->assertNotNull($user->google_id);
        $this->assertTrue($user->usesGoogleOnly());
    }

    public function test_an_existing_email_user_is_linked_instead_of_duplicated(): void
    {
        $existing = User::factory()->create(['email' => 'john@example.com']);

        $this->get(route('login.google'));
        $this->get(route('login.google.callback', ['state' => session('social_oauth.state'), 'code' => FakeGoogleLoginProvider::codeFor('john@example.com', 'John')]));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::where('email', 'john@example.com')->count());
        $this->assertNotNull($existing->fresh()->google_id);
        $this->assertFalse($existing->fresh()->usesGoogleOnly()); // still has a password
    }

    public function test_a_returning_google_user_matches_on_google_id(): void
    {
        $this->get(route('login.google'));
        $this->get(route('login.google.callback', ['state' => session('social_oauth.state'), 'code' => FakeGoogleLoginProvider::codeFor('back@example.com', 'Back')]));
        $user = User::where('email', 'back@example.com')->sole();

        $this->post(route('logout'));
        $this->get(route('login.google'));
        $this->get(route('login.google.callback', ['state' => session('social_oauth.state'), 'code' => FakeGoogleLoginProvider::codeFor('back@example.com', 'Back')]));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_a_forged_state_or_denied_consent_is_rejected(): void
    {
        $this->get(route('login.google'));
        $this->get(route('login.google.callback', ['state' => 'forged', 'code' => FakeGoogleLoginProvider::codeFor('x@example.com', 'X')]))
            ->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();

        $this->get(route('login.google'));
        $this->get(route('login.google.callback', ['state' => session('social_oauth.state'), 'error' => 'access_denied']))
            ->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_only_users_cannot_sign_in_with_a_password(): void
    {
        User::factory()->create(['email' => 'g@example.com', 'google_id' => 'g-1', 'password' => null]);

        Livewire::test(Login::class)
            ->set('email', 'g@example.com')
            ->set('password', 'anything')
            ->call('login')
            ->assertHasErrors('email')
            ->assertSee('signs in with Google');

        $this->assertGuest();
    }

    public function test_the_fake_google_screen_submits_back_to_the_callback(): void
    {
        $this->get(route('login.google'));

        $this->post(route('oauth.fake.google.decide'), ['state' => session('social_oauth.state'), 'email' => 'demo@example.com', 'name' => 'Demo', 'decision' => 'allow'])
            ->assertRedirect();
    }
}
