<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CaregiverInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $caregiver;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($caregiver, $password)
    {
        $this->caregiver = $caregiver;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welkom bij Ouderen Alarm - 14 dagen gratis proefperiode',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.caregivers.invitation',
            with: [
                'caregiver' => $this->caregiver,
                'password' => $this->password,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}