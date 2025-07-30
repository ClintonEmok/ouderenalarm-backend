<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;

class CustomerInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $password;
    protected string $subjectLine;

    public function __construct($customer, $password)
    {
        $this->customer = $customer;
        $this->password = $password;

        $this->subjectLine = collect([
            'Welkom bij Ouderen Alarm – jouw gratis proefperiode start nu',
            'Ouderen Alarm: Je proefaccount staat klaar',
            'Jouw gratis proefperiode is gestart!',
            'Ouderen Alarm: Direct aan de slag met je account',
        ])->random();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer.invitation',
            with: [
                'customer' => $this->customer,
                'password' => $this->password,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path('/documenten/handleiding.pdf'))
                ->as('Handleiding.pdf')
                ->withMime('application/pdf'),
        ];
    }
}