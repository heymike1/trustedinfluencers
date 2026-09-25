<?php

namespace Tests\Feature;

use App\Actions\Admin\MergeCreators;
use App\Enums\CreatorStatus;
use App\Enums\Platform;
use App\Livewire\Admin\CreatorDetail;
use App\Livewire\Admin\Creators;
use App\Models\Creator;
use App\Models\CreatorContactRequest;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_non_admins_are_refused(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_pages_render(): void
    {
        $creator = Creator::factory()->claimedBy()->create();
        CreatorSocialAccount::factory()->for($creator)->connected()->create();

        foreach (['admin.dashboard', 'admin.creators', 'admin.users', 'admin.claims', 'admin.duplicates', 'admin.accounts', 'admin.content', 'admin.requests', 'admin.categories', 'admin.sponsors'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }

        $this->actingAs($this->admin)->get(route('admin.creators.show', $creator))->assertOk()->assertSee($creator->name);
    }

    public function test_admin_can_hide_and_delete_spam_profiles(): void
    {
        $spam = Creator::factory()->create();
        $claimed = Creator::factory()->claimedBy()->create();

        Livewire::actingAs($this->admin)->test(Creators::class)->call('hide', $spam->id);
        $this->assertSame(CreatorStatus::Hidden, $spam->fresh()->status);

        Livewire::actingAs($this->admin)->test(Creators::class)->call('destroy', $spam->id);
        $this->assertNull($spam->fresh());

        Livewire::actingAs($this->admin)->test(Creators::class)->call('destroy', $claimed->id)->assertSee('cannot be deleted');
        $this->assertNotNull($claimed->fresh());
    }

    public function test_merging_moves_accounts_and_requests_and_leaves_a_redirecting_tombstone(): void
    {
        $target = Creator::factory()->create(['bio' => null]);
        CreatorSocialAccount::factory()->for($target)->platform(Platform::YouTube, 'john')->create();

        $duplicate = Creator::factory()->create(['bio' => 'Kept bio']);
        CreatorSocialAccount::factory()->for($duplicate)->platform(Platform::X, 'john')->create();
        CreatorContactRequest::factory()->for($duplicate)->create();

        app(MergeCreators::class)->handle($duplicate, $target);

        $target->refresh();
        $duplicate->refresh();

        $this->assertSame(2, $target->socialAccounts()->count());
        $this->assertSame(1, $target->contactRequests()->count());
        $this->assertSame('Kept bio', $target->bio);
        $this->assertSame(CreatorStatus::Merged, $duplicate->status);
        $this->assertTrue($duplicate->mergedInto->is($target));
        $this->get(route('creators.show', $duplicate))->assertRedirect(route('creators.show', $target));
    }

    public function test_merging_keeps_the_connected_account_when_platforms_collide(): void
    {
        $target = Creator::factory()->create();
        $unconnected = CreatorSocialAccount::factory()->for($target)->platform(Platform::YouTube, 'john')->create();

        $owner = User::factory()->create();
        $duplicate = Creator::factory()->claimedBy($owner)->create();
        $connected = CreatorSocialAccount::factory()->for($duplicate)->platform(Platform::YouTube, 'john2')->connected()->create();

        app(MergeCreators::class)->handle($duplicate, $target);

        $this->assertNull($unconnected->fresh());
        $this->assertTrue($connected->fresh()->creator->is($target));
        $this->assertTrue($target->fresh()->user->is($owner));
    }

    public function test_merging_two_profiles_claimed_by_different_users_is_refused(): void
    {
        $a = Creator::factory()->claimedBy()->create();
        $b = Creator::factory()->claimedBy()->create();

        $this->expectException(ValidationException::class);
        app(MergeCreators::class)->handle($a, $b);
    }

    public function test_admin_can_release_a_claim_to_resolve_a_dispute(): void
    {
        $creator = Creator::factory()->claimedBy()->create(['has_verified_metrics' => true]);
        $account = CreatorSocialAccount::factory()->for($creator)->connected()->create();

        Livewire::actingAs($this->admin)->test(CreatorDetail::class, ['creator' => $creator])->call('releaseClaim')->assertSee('Claim released');

        $creator->refresh();
        $this->assertFalse($creator->isClaimed());
        $this->assertNull($creator->user_id);
        $this->assertNull($account->fresh()->access_token);
        $this->assertFalse($creator->has_verified_metrics);
    }

    public function test_duplicates_page_flags_same_names(): void
    {
        Creator::factory()->create(['name' => 'John Smith']);
        Creator::factory()->create(['name' => 'john  smith']);

        $this->actingAs($this->admin)->get(route('admin.duplicates'))->assertSee('Same name');
    }
}
