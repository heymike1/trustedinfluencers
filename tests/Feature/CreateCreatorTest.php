<?php

namespace Tests\Feature;

use App\Actions\Creators\CreateCreator;
use App\Actions\Creators\DuplicateCreatorException;
use App\Enums\Platform;
use App\Jobs\SyncPublicProfile;
use App\Livewire\AddCreator;
use App\Models\Creator;
use App\Models\CreatorCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CreateCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_add_a_creator_without_any_user(): void
    {
        Queue::fake();
        $category = CreatorCategory::factory()->create(['name' => 'Finance']);

        $creator = app(CreateCreator::class)->handle('John Smith', Platform::YouTube, 'https://youtube.com/@JohnSmith', $category->id);

        $this->assertNull($creator->user_id);
        $this->assertNull($creator->claimed_at);
        $this->assertSame('john-smith', $creator->slug);
        $this->assertSame($category->id, $creator->creator_category_id);

        $account = $creator->socialAccounts()->sole();
        $this->assertSame(Platform::YouTube, $account->platform);
        $this->assertSame('johnsmith', $account->handle);
        $this->assertSame('https://www.youtube.com/@johnsmith', $account->profile_url);

        Queue::assertPushed(SyncPublicProfile::class, fn ($job) => $job->account->is($account));
    }

    public function test_the_same_account_written_differently_is_rejected_as_a_duplicate(): void
    {
        Queue::fake();
        $action = app(CreateCreator::class);
        $existing = $action->handle('John Smith', Platform::YouTube, '@john');

        foreach (['https://youtube.com/@john', 'youtube.com/@John', 'john'] as $variant) {
            try {
                $action->handle('Johnny', Platform::YouTube, $variant);
                $this->fail("Expected duplicate for {$variant}");
            } catch (DuplicateCreatorException $e) {
                $this->assertTrue($e->existing->is($existing));
            }
        }

        $this->assertSame(1, Creator::count());
    }

    public function test_the_same_handle_on_another_platform_is_a_different_account(): void
    {
        Queue::fake();
        $action = app(CreateCreator::class);
        $action->handle('John', Platform::YouTube, '@john');
        $action->handle('John', Platform::X, '@john');

        $this->assertSame(2, Creator::count());
        $this->assertSame('john-2', Creator::latest('id')->first()->slug);
    }

    public function test_invalid_handles_are_rejected(): void
    {
        $this->expectException(ValidationException::class);

        app(CreateCreator::class)->handle('Nope', Platform::Instagram, 'https://youtube.com/@john');
    }

    public function test_the_add_creator_page_creates_and_redirects(): void
    {
        Queue::fake();

        Livewire::test(AddCreator::class)
            ->set('name', 'Lena Fischer')
            ->set('platform', 'instagram')
            ->set('handle', 'https://www.instagram.com/lena.fit/')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('creators.show', 'lena-fischer'));

        $this->assertDatabaseHas('creator_social_accounts', ['platform' => 'instagram', 'handle' => 'lena.fit']);
    }

    public function test_the_add_creator_page_points_at_the_existing_profile_for_duplicates(): void
    {
        Queue::fake();
        $existing = app(CreateCreator::class)->handle('Lena Fischer', Platform::Instagram, 'lena.fit');

        Livewire::test(AddCreator::class)
            ->set('name', 'Lena F.')
            ->set('platform', 'instagram')
            ->set('handle', '@Lena.Fit')
            ->call('submit')
            ->assertHasErrors('handle')
            ->assertSet('existingCreatorId', $existing->id)
            ->assertSee('already listed');
    }

    public function test_pasting_a_url_switches_the_platform(): void
    {
        Livewire::test(AddCreator::class)
            ->set('platform', 'youtube')
            ->set('handle', 'https://x.com/marcus')
            ->assertSet('platform', 'x');
    }
}
