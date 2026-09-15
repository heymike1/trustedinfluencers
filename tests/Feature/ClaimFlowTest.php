<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\ConnectionStatus;
use App\Enums\Platform;
use App\Jobs\CalculateCreatorPerformance;
use App\Jobs\SyncCreatorContent;
use App\Jobs\SyncCreatorMetrics;
use App\Jobs\SyncCreatorSocialProfile;
use App\Models\Creator;
use App\Models\CreatorClaim;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use App\Social\Fake\FakeConnector;
use App\Social\Fake\FakeDataGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ClaimFlowTest extends TestCase
{
    use RefreshDatabase;

    private Creator $creator;

    private CreatorSocialAccount $account;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = Creator::factory()->create(['name' => 'John Smith', 'slug' => 'john-smith']);
        $this->account = CreatorSocialAccount::factory()->for($this->creator)->platform(Platform::YouTube, 'johnsmith')->create();
        $this->user = User::factory()->create();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('creators.claim', $this->creator))->assertRedirect(route('login'));
    }

    public function test_the_claim_page_lists_the_accounts_to_verify(): void
    {
        $this->actingAs($this->user)
            ->get(route('creators.claim', $this->creator))
            ->assertOk()
            ->assertSee('Verify with YouTube')
            ->assertSee('@johnsmith');
    }

    public function test_starting_a_claim_records_it_and_redirects_to_the_provider(): void
    {
        $response = $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));

        $claim = CreatorClaim::sole();
        $this->assertSame(ClaimStatus::Pending, $claim->status);
        $this->assertTrue($claim->user->is($this->user));

        $response->assertRedirect();
        $this->assertStringStartsWith(route('oauth.fake.authorize', ['platform' => 'youtube']), $response->headers->get('Location'));
        $this->assertStringContainsString('hint=johnsmith', $response->headers->get('Location'));
    }

    public function test_successful_oauth_verifies_ownership_links_the_user_and_queues_the_import(): void
    {
        Bus::fake();

        $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));
        $state = $this->pendingState();

        $this->actingAs($this->user)
            ->get(route('oauth.callback', ['platform' => 'youtube', 'state' => $state, 'code' => FakeConnector::codeFor('JohnSmith')]))
            ->assertRedirect(route('creators.show', $this->creator))
            ->assertSessionHas('success');

        $this->creator->refresh();
        $this->account->refresh();
        $claim = CreatorClaim::sole();

        $this->assertTrue($this->creator->isClaimed());
        $this->assertTrue($this->creator->user->is($this->user));
        $this->assertSame(ClaimStatus::Verified, $claim->status);
        $this->assertSame(app(FakeDataGenerator::class)->providerAccountId(Platform::YouTube, 'johnsmith'), $this->account->provider_account_id);
        $this->assertSame($this->account->provider_account_id, $claim->returned_provider_account_id);
        $this->assertSame(ConnectionStatus::Importing, $this->account->connection_status);
        $this->assertNotNull($this->account->access_token);
        $this->assertNotNull($this->account->connected_at);

        Bus::assertChained([
            SyncCreatorSocialProfile::class,
            SyncCreatorContent::class,
            SyncCreatorMetrics::class,
            CalculateCreatorPerformance::class,
        ]);
    }

    public function test_signing_in_to_a_different_account_fails_the_claim(): void
    {
        Bus::fake();

        $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));
        $state = $this->pendingState();

        $this->actingAs($this->user)
            ->get(route('oauth.callback', ['platform' => 'youtube', 'state' => $state, 'code' => FakeConnector::codeFor('someoneelse')]))
            ->assertRedirect(route('creators.claim', $this->creator))
            ->assertSessionHas('error', fn ($msg) => str_contains($msg, '@someoneelse') && str_contains($msg, '@johnsmith'));

        $this->creator->refresh();
        $claim = CreatorClaim::sole();

        $this->assertFalse($this->creator->isClaimed());
        $this->assertNull($this->creator->user_id);
        $this->assertSame(ClaimStatus::Failed, $claim->status);
        $this->assertSame('someoneelse', $claim->returned_handle);
        $this->assertSame(ConnectionStatus::Unconnected, $this->account->fresh()->connection_status);
        $this->assertNull($this->account->fresh()->access_token);

        Bus::assertNothingDispatched();
    }

    public function test_ownership_is_matched_on_the_stored_provider_account_id_when_present(): void
    {
        Bus::fake();
        // The profile was created from a /channel/UC… URL, so we know the canonical id up front.
        $this->account->update(['provider_account_id' => 'UC_someone_elses_channel_id']);

        $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));
        $state = $this->pendingState();

        // Same handle, but the fake provider returns a different channel id → mismatch.
        $this->actingAs($this->user)
            ->get(route('oauth.callback', ['platform' => 'youtube', 'state' => $state, 'code' => FakeConnector::codeFor('johnsmith')]))
            ->assertSessionHas('error');

        $this->assertFalse($this->creator->fresh()->isClaimed());
        $this->assertSame(ClaimStatus::Failed, CreatorClaim::sole()->status);
    }

    public function test_a_forged_or_expired_state_is_rejected(): void
    {
        $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));

        $this->actingAs($this->user)
            ->get(route('oauth.callback', ['platform' => 'youtube', 'state' => 'forged', 'code' => FakeConnector::codeFor('johnsmith')]))
            ->assertRedirect(route('account'))
            ->assertSessionHas('error');

        $this->assertFalse($this->creator->fresh()->isClaimed());
    }

    public function test_denying_consent_returns_to_the_claim_page(): void
    {
        $this->actingAs($this->user)->post(route('creators.claim.start', [$this->creator, $this->account]));
        $state = $this->pendingState();

        $this->actingAs($this->user)
            ->get(route('oauth.callback', ['platform' => 'youtube', 'state' => $state, 'error' => 'access_denied']))
            ->assertRedirect(route('creators.claim', $this->creator))
            ->assertSessionHas('error');
    }

    public function test_a_claimed_profile_cannot_be_claimed_again(): void
    {
        $this->creator->update(['user_id' => User::factory()->create()->id, 'claimed_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('creators.claim', $this->creator))
            ->assertOk()
            ->assertSee('already been claimed')
            ->assertDontSee('Verify with YouTube');

        $this->actingAs($this->user)
            ->post(route('creators.claim.start', [$this->creator, $this->account]))
            ->assertRedirect(route('creators.claim', $this->creator))
            ->assertSessionHas('error');

        $this->assertSame(0, CreatorClaim::count());
    }

    public function test_a_user_who_already_owns_a_profile_cannot_claim_another(): void
    {
        Creator::factory()->claimedBy($this->user)->create();

        $this->actingAs($this->user)
            ->post(route('creators.claim.start', [$this->creator, $this->account]))
            ->assertSessionHas('error');

        $this->assertSame(0, CreatorClaim::count());
    }

    public function test_the_unclaimed_profile_shows_the_claim_call_to_action(): void
    {
        $this->get(route('creators.show', $this->creator))
            ->assertOk()
            ->assertSee('Is this you?')
            ->assertSee('Claim this profile')
            ->assertSee('Public info only')
            ->assertDontSee('Verified through');
    }

    private function pendingState(): string
    {
        return session('social_oauth.state');
    }
}
