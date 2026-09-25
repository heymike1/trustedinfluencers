<?php

namespace Tests\Feature;

use App\Livewire\Account\LoginSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AccountLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_name_and_email_can_be_changed(): void
    {
        $user = User::factory()->create(['name' => 'Mike', 'email' => 'old@example.com']);

        Livewire::actingAs($user)->test(LoginSettings::class)
            ->set('name', 'Mike R')
            ->set('email', 'new@example.com')
            ->call('saveAccount')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Mike R', $user->name);
        $this->assertSame('new@example.com', $user->email);
    }

    public function test_an_email_already_in_use_is_refused(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(LoginSettings::class)
            ->set('email', 'taken@example.com')
            ->call('saveAccount')
            ->assertHasErrors('email');
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        Livewire::actingAs($user)->test(LoginSettings::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('savePassword')
            ->assertHasErrors('current_password');

        Livewire::actingAs($user)->test(LoginSettings::class)
            ->set('current_password', 'old-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('savePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_a_google_only_account_can_set_a_first_password(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'g-1']);

        Livewire::actingAs($user)->test(LoginSettings::class)
            ->assertSee('Set a password')
            ->set('password', 'first-password')
            ->set('password_confirmation', 'first-password')
            ->call('savePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('first-password', $user->fresh()->password));
        $this->assertSame('g-1', $user->fresh()->google_id);
    }

    public function test_the_screen_shows_how_you_sign_in(): void
    {
        $user = User::factory()->create(['email' => 'mike@example.com', 'google_id' => 'g-2']);

        $this->actingAs($user)->get(route('account.settings'))
            ->assertOk()
            ->assertSee('mike@example.com')
            ->assertSee('Connected');
    }
}
