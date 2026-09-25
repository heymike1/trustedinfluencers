<?php

namespace Tests\Feature;

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\ContactRequests;
use App\Livewire\Admin\CreatorDetail;
use App\Livewire\Admin\Users;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The admin panel edits real records, so each screen is exercised through its own component.
 */
class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_a_creator_profile_can_be_edited(): void
    {
        $creator = Creator::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
        $category = CreatorCategory::factory()->create();

        Livewire::actingAs($this->admin)->test(CreatorDetail::class, ['creator' => $creator])
            ->set('form.name', 'New Name')
            ->set('form.slug', 'new-name')
            ->set('form.creator_category_id', (string) $category->id)
            ->set('form.location', 'Rotterdam')
            ->set('form.is_listed', false)
            ->call('save')
            ->assertHasNoErrors();

        $creator->refresh();
        $this->assertSame('New Name', $creator->name);
        $this->assertSame('new-name', $creator->slug);
        $this->assertSame($category->id, $creator->creator_category_id);
        $this->assertFalse($creator->is_listed);
    }

    public function test_a_profile_can_be_transferred_to_another_user(): void
    {
        $creator = Creator::factory()->create();
        $owner = User::factory()->create(['email' => 'owner@example.com']);

        Livewire::actingAs($this->admin)->test(CreatorDetail::class, ['creator' => $creator])
            ->set('transferTo', 'owner@example.com')
            ->call('transfer')
            ->assertHasNoErrors();

        $this->assertTrue($creator->fresh()->isOwnedBy($owner));
    }

    public function test_categories_can_be_added_reordered_and_removed(): void
    {
        Livewire::actingAs($this->admin)->test(Categories::class)
            ->set('name', 'Podcasting')
            ->set('sort_order', 1)
            ->call('save')
            ->assertHasNoErrors();

        $category = CreatorCategory::sole();
        $this->assertSame('podcasting', $category->slug);

        Livewire::actingAs($this->admin)->test(Categories::class)->call('destroy', $category->id);
        $this->assertSame(0, CreatorCategory::count());
    }

    public function test_a_category_in_use_is_not_deleted(): void
    {
        $category = CreatorCategory::factory()->create(['name' => 'Finance']);
        Creator::factory()->create(['creator_category_id' => $category->id]);

        Livewire::actingAs($this->admin)->test(Categories::class)
            ->call('destroy', $category->id)
            ->assertSee('still has 1 creators');

        $this->assertSame(1, CreatorCategory::count());
    }

    public function test_users_can_be_edited_and_deleted(): void
    {
        $user = User::factory()->create(['name' => 'Jane', 'email' => 'jane@example.com']);
        $creator = Creator::factory()->claimedBy($user)->create();

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $user->id)
            ->set('name', 'Jane Doe')
            ->set('is_admin', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Jane Doe', $user->fresh()->name);
        $this->assertTrue($user->fresh()->is_admin);

        Livewire::actingAs($this->admin)->test(Users::class)->call('destroy', $user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertFalse($creator->fresh()->isClaimed());
    }

    public function test_an_admin_cannot_delete_or_demote_themselves(): void
    {
        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('edit', $this->admin->id)
            ->set('is_admin', false)
            ->call('save')
            ->assertHasErrors('is_admin');

        Livewire::actingAs($this->admin)->test(Users::class)
            ->call('destroy', $this->admin->id)
            ->assertSee('cannot delete your own account');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_contact_requests_can_be_read_and_deleted(): void
    {
        $creator = Creator::factory()->create();
        $request = CreatorContactRequest::create([
            'creator_id' => $creator->id, 'name' => 'Sarah', 'email' => 'sarah@brand.example',
            'subject' => 'Campaign', 'message' => 'Hello there',
        ]);

        Livewire::actingAs($this->admin)->test(ContactRequests::class)
            ->call('toggleRead', $request->id);
        $this->assertNotNull($request->fresh()->read_at);

        Livewire::actingAs($this->admin)->test(ContactRequests::class)->call('destroy', $request->id);
        $this->assertSame(0, CreatorContactRequest::count());
    }
}
