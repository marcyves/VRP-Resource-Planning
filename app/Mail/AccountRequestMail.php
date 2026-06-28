<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.landing_request_mail_subject', [
                'company' => $this->payload['company_name'],
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.account-request',
        );
    }
}
