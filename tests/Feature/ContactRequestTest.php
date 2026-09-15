<?php

namespace Tests\Feature;

use App\Livewire\ContactCreatorForm;
use App\Mail\ContactRequestReceived;
use App\Models\Creator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Sam Rivera',
        'email' => 'sam@brightpath.example',
        'company' => 'Brightpath',
        'subject' => 'Sponsored video',
        'message' => 'We would love to work with you on our Q4 launch. Budget is flexible.',
    ];

    public function test_a_request_to_a_claimed_creator_is_stored_and_emailed_to_the_owner(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['email' => 'john@example.com']);
        $creator = Creator::factory()->claimedBy($owner)->create();

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])
            ->set($this->payload)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true)
            ->assertSee('has your message');

        $request = $creator->contactRequests()->sole();
        $this->assertSame('Sponsored video', $request->subject);
        $this->assertNotNull($request->delivered_at);

        Mail::assertQueued(ContactRequestReceived::class, fn ($mail) => $mail->hasTo('john@example.com') && $mail->contactRequest->is($request));
    }

    public function test_a_custom_contact_email_takes_precedence(): void
    {
        Mail::fake();
        $creator = Creator::factory()->claimedBy()->create(['contact_email' => 'bookings@john.example']);

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])->set($this->payload)->call('submit');

        Mail::assertQueued(ContactRequestReceived::class, fn ($mail) => $mail->hasTo('bookings@john.example'));
    }

    public function test_a_request_to_an_unclaimed_creator_is_stored_but_not_emailed(): void
    {
        Mail::fake();
        $creator = Creator::factory()->create(['contact_email' => 'public@example.com']);

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])
            ->set($this->payload)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSee('kept your message');

        $this->assertNull($creator->contactRequests()->sole()->delivered_at);
        Mail::assertNothingQueued();
    }

    public function test_unclaimed_forwarding_can_be_enabled_explicitly(): void
    {
        Mail::fake();
        config(['social.contact.forward_to_public_email' => true]);
        $creator = Creator::factory()->create(['contact_email' => 'public@example.com']);

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])->set($this->payload)->call('submit');

        Mail::assertQueued(ContactRequestReceived::class, fn ($mail) => $mail->hasTo('public@example.com'));
    }

    public function test_stored_requests_become_visible_after_the_creator_claims(): void
    {
        Mail::fake();
        $creator = Creator::factory()->create();
        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])->set($this->payload)->call('submit');

        $owner = User::factory()->create();
        $creator->update(['user_id' => $owner->id, 'claimed_at' => now()]);

        $this->actingAs($owner)->get(route('account.requests'))->assertOk()->assertSee('Sponsored video')->assertSee('Brightpath');
    }

    public function test_disabled_contact_hides_the_form(): void
    {
        $creator = Creator::factory()->claimedBy()->create(['contact_enabled' => false]);

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])
            ->assertSee('taking contact requests')
            ->assertDontSee('Send request');
    }

    public function test_validation(): void
    {
        $creator = Creator::factory()->create();

        Livewire::test(ContactCreatorForm::class, ['creator' => $creator])
            ->set('email', 'nope')
            ->set('message', 'short')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'subject', 'message']);
    }
}
