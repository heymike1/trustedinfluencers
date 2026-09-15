<?php

namespace App\Actions\Contact;

use App\Mail\ContactRequestReceived;
use App\Models\Creator;
use App\Models\CreatorContactRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Stores a contact request and delivers it when there is a trustworthy address to deliver to.
 *
 * Claimed profile → the owner's login email (and the platform inbox).
 * Unclaimed profile → stored only, unless forwarding to the submitted public email is enabled.
 *                     Either way the request becomes visible to the creator once they claim.
 */
class SubmitContactRequest
{
    public function handle(Creator $creator, array $data, ?string $ip = null): CreatorContactRequest
    {
        if (! $creator->contact_enabled) {
            throw ValidationException::withMessages(['message' => 'This creator isn\'t taking contact requests right now.']);
        }

        $request = $creator->contactRequests()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $ip,
        ]);

        $recipient = $this->recipientFor($creator);

        if ($recipient) {
            Mail::to($recipient)->queue(new ContactRequestReceived($request));
            $request->forceFill(['delivered_at' => now()])->save();
        }

        return $request;
    }

    private function recipientFor(Creator $creator): ?string
    {
        if ($creator->isClaimed()) {
            return $creator->contact_email ?: $creator->user?->email;
        }

        if (config('social.contact.forward_to_public_email') && $creator->contact_email) {
            return $creator->contact_email;
        }

        return null;
    }
}
