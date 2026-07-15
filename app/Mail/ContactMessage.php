<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $contactSubject,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            // From stays the app's own configured sender. Sending *as* the visitor
            // would fail SPF/DKIM and land the mail in spam — the visitor's address
            // belongs in Reply-To, not From.
            replyTo: [new Address($this->senderEmail, $this->senderName)],
            subject: 'Contact form: '.$this->contactSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-message',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'contactSubject' => $this->contactSubject,
                'body' => $this->body,
            ],
        );
    }
}
