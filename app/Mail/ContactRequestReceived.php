<?php

namespace App\Mail;

use App\Models\CreatorContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactRequestReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public CreatorContactRequest $contactRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New contact request: '.$this->contactRequest->subject,
            replyTo: [$this->contactRequest->email],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.contact-request-received');
    }
}
