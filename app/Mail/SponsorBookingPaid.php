<?php

namespace App\Mail;

use App\Models\SponsorSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SponsorBookingPaid extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public SponsorSlot $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your sponsor spot · '.config('app.name'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.sponsor-booking-paid');
    }
}
