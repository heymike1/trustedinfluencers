<?php

namespace Tests\Feature;

use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Livewire\Account\Connections;
use App\Livewire\Account\EditProfile;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorPerformanceMetric;
use App\Models\CreatorSocialAccount;
use App\Models\SocialContent;
use App\Models\User;
use App\Social\Fake\FakeConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreatorAccountTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Creator $creator;

    private CreatorSocialAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->creator = Creator::factory()->claimedBy($this->owner)->create(['name' => 'John Smith', 'slug' => 'john-smith', 'has_verified_metrics' => true, 'median_views' => 73_000]);
        $this->account = CreatorSocialAccount::factory()->for($this->creator)->platform(Platform::YouTube, 'johnsmith')->connected()->create(['follower_count' => 124_000]);
    }

    public function test_the_owner_can_edit_public_profile_fields(): void
    {
        $category = CreatorCategory::factory()->create();

        Livewire::actingAs($this->owner)->test(EditProfile::class)
            ->set('name', 'John A. Smith')
            ->set('bio', 'Finance, simply.')
            ->set('category', (string) $category->id)
            ->set('website', 'https://johnsmith.money')
            ->set('contact_email', 'hello@johnsmith.money')
            ->set('contact_enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->creator->refresh();
        $this->assertSame('John A. Smith', $this->creator->name);
        $this->assertSame('john-a-smith', $this->creator->slug);
        $this->assertSame('Finance, simply.', $this->creator->bio);
        $this->assertSame($category->id, $this->creator->creator_category_id);
        $this->assertFalse($this->creator->contact_enabled);
    }

    public function test_verified_metrics_cannot_be_edited_through_the_profile_form(): void
    {
        Livewire::actingAs($this->owner)->test(EditProfile::class)
            ->set('name', 'John Smith')
            ->call('save')
            ->assertHasNoErrors();

        $this->creator->refresh();
        $this->account->refresh();

        $this->assertSame(73_000, $this->creator->median_views);
        $this->assertSame(124_000, $this->account->follower_count);
        $this->assertTrue($this->creator->has_verified_metrics);
    }

    public function test_users_without_a_profile_see_the_empty_state(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertSee('haven’t claimed a creator profile');
    }

    public function test_another_user_cannot_edit_or_manage_the_profile(): void
    {
        $stranger = User::factory()->create();

        // The stranger's dashboard shows nothing to edit…
        Livewire::actingAs($stranger)->test(EditProfile::class)->assertSet('creator', null);

        // …and even a crafted call is refused.
        Livewire::actingAs($stranger)->test(Connections::class)->call('disconnect', $this->account->id)->assertForbidden();

        $this->assertSame(ConnectionStatus::Connected, $this->account->fresh()->connection_status);
    }

    public function test_another_user_cannot_start_oauth_for_the_account(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('account.connections.connect', $this->account))
            ->assertRedirect(route('account.connections'))
            ->assertSessionHas('error');
    }

    public function test_manual_sync_is_rate_limited(): void
    {
        config(['social.sync.manual_cooldown_minutes' => 60]);

        Livewire::actingAs($this->owner)->test(Connections::class)
            ->call('syncNow', $this->account->id)
            ->assertSee('sync started');

        $this->assertSame(ConnectionStatus::Connected, $this->account->fresh()->connection_status);
        $this->assertNotNull($this->account->fresh()->sync_requested_at);

        Livewire::actingAs($this->owner)->test(Connections::class)
            ->call('syncNow', $this->account->id)
            ->assertSee('You can sync again');
    }

    public function test_disconnecting_removes_verified_data_but_keeps_the_public_profile(): void
    {
        SocialContent::factory()->for($this->account, 'socialAccount')->withMetrics(['views' => 10])->count(3)->create();
        $this->account->snapshots()->create(['captured_at' => now(), 'metrics' => ['followers' => 1]]);
        CreatorPerformanceMetric::create([
            'creator_id' => $this->creator->id, 'creator_social_account_id' => $this->account->id, 'platform' => Platform::YouTube,
            'content_type' => 'video', 'calculation_window' => 'last_20', 'sample_size' => 3, 'median_views' => 10, 'calculated_at' => now(),
        ]);

        Livewire::actingAs($this->owner)->test(Connections::class)
            ->call('disconnect', $this->account->id)
            ->assertSee('disconnected');

        $this->account->refresh();
        $this->creator->refresh();

        $this->assertSame(ConnectionStatus::Disconnected, $this->account->connection_status);
        $this->assertNull($this->account->access_token);
        $this->assertNull($this->account->refresh_token);
        $this->assertSame(0, $this->account->contents()->count());
        $this->assertSame(0, $this->account->snapshots()->count());
        $this->assertSame(0, $this->account->performanceMetrics()->count());

        $this->assertSame('johnsmith', $this->account->handle);
        $this->assertSame(124_000, $this->account->follower_count);
        $this->assertTrue($this->creator->isClaimed());
        $this->assertFalse($this->creator->has_verified_metrics);
        $this->assertNull($this->creator->median_views);
    }

    public function test_the_owner_can_add_and_then_connect_another_platform(): void
    {
        Livewire::actingAs($this->owner)->test(Connections::class)
            ->set('newPlatform', 'x')
            ->set('newHandle', 'https://x.com/JohnSmith')
            ->call('addAccount')
            ->assertHasNoErrors();

        $x = $this->creator->socialAccounts()->where('platform', Platform::X)->sole();
        $this->assertSame('johnsmith', $x->handle);
        $this->assertSame(ConnectionStatus::Unconnected, $x->connection_status);

        $this->actingAs($this->owner)->post(route('account.connections.connect', $x))->assertRedirect();
        $state = session('social_oauth.state');

        $this->actingAs($this->owner)
            ->get(route('oauth.callback', ['platform' => 'x', 'state' => $state, 'code' => FakeConnector::codeFor('johnsmith')]))
            ->assertRedirect(route('account.connections'))
            ->assertSessionHas('success');

        $this->assertSame(ConnectionStatus::Connected, $x->fresh()->connection_status); // sync queue runs inline
        $this->assertNotNull($x->fresh()->last_synced_at);
    }

    public function test_adding_an_account_listed_elsewhere_is_rejected(): void
    {
        CreatorSocialAccount::factory()->platform(Platform::X, 'johnsmith')->create();

        Livewire::actingAs($this->owner)->test(Connections::class)
            ->set('newPlatform', 'x')
            ->set('newHandle', '@johnsmith')
            ->call('addAccount')
            ->assertHasErrors('newHandle');
    }
}
