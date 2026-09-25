<?php

namespace Tests\Feature;

use App\Livewire\Admin\SettingsScreen;
use App\Livewire\Admin\Sponsors;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_sponsor_rail_settings_are_saved_and_applied(): void
    {
        Livewire::actingAs($this->admin)->test(Sponsors::class)
            ->set('settings.slots_per_rail', 2)
            ->set('settings.price', '€400')
            ->set('settings.days', 14)
            ->set('settings.advance_price', '€1200')
            ->set('settings.contact', 'ads@example.com')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $this->assertSame(2, config('social.sponsors.slots_per_rail'));
        $this->assertSame('€400', config('social.sponsors.price'));
        $this->assertSame(14, config('social.sponsors.days'));

        // A fresh request reads them back from the database, not from the config file.
        $this->get('/')->assertOk()->assertSee('€400 / 14 days');
    }

    public function test_the_platform_switches_survive_the_request(): void
    {
        // The suite runs with all three on, so toggling takes one off.
        Livewire::actingAs($this->admin)->test(SettingsScreen::class)
            ->call('togglePlatform', 'youtube')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNotContains('youtube', config('social.enabled_platforms'));
        $this->assertContains('instagram', config('social.enabled_platforms'));
        $this->assertTrue(app(Settings::class)->isOverridden('social.enabled_platforms'));
    }

    public function test_sync_settings_are_validated(): void
    {
        Livewire::actingAs($this->admin)->test(SettingsScreen::class)
            ->set('form.refresh_every_hours', 0)
            ->call('save')
            ->assertHasErrors('form.refresh_every_hours');
    }

    public function test_resetting_falls_back_to_the_config_files(): void
    {
        $settings = app(Settings::class);
        $settings->set('social.sponsors.price', '€999');

        Livewire::actingAs($this->admin)->test(SettingsScreen::class)->call('resetToFile');

        $this->assertFalse($settings->isOverridden('social.sponsors.price'));
    }

    public function test_only_listed_keys_can_be_stored(): void
    {
        $this->expectException(HttpException::class);

        app(Settings::class)->set('app.key', 'nope');
    }
}
