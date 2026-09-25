<?php

namespace Tests\Feature;

use App\Actions\Creators\CreateCreator;
use App\Enums\Platform;
use App\Livewire\Account\Connections;
use App\Livewire\AddCreator;
use App\Models\Creator;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A platform left out of social.enabled_platforms keeps its existing data but is not offered
 * anywhere, and cannot be added, claimed or connected.
 */
class DisabledPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['social.enabled_platforms' => ['instagram', 'x']]);
    }

    public function test_the_platform_is_not_offered_anywhere(): void
    {
        $this->assertFalse(Platform::YouTube->isEnabled());
        $this->assertSame([Platform::Instagram, Platform::X], Platform::enabled());

        Livewire::test(AddCreator::class)->assertSee('Instagram')->assertDontSee('YouTube');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee(route('creators.index', ['platform' => 'youtube']), false);
    }

    public function test_adding_a_disabled_platform_is_refused(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateCreator::class)->handle('John Smith', Platform::YouTube, '@johnsmith');
    }

    public function test_an_existing_account_stays_visible_but_cannot_be_claimed_or_connected(): void
    {
        $owner = User::factory()->create();
        $creator = Creator::factory()->claimedBy($owner)->create(['name' => 'John Smith', 'slug' => 'john-smith']);
        CreatorSocialAccount::factory()->for($creator)->platform(Platform::YouTube, 'johnsmith')->create(['follower_count' => 124_000]);

        $this->get(route('creators.show', $creator))->assertOk()->assertSee('@johnsmith');

        Livewire::actingAs($owner)->test(Connections::class)
            ->assertSee('@johnsmith')
            ->assertSee('switched off for now')
            ->assertDontSee('Connect YouTube');
    }
}
