<?php

namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class InviteCaregiverMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Invite $invite, public bool $isNewUser = false,)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isNewUser
                ? 'Complete your caregiver registration'
                : 'You’ve been invited as a caregiver',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
//        TODO: Fix url
//        TODO: Fix email
        return new Content(
            markdown: $this->isNewUser
                ? 'emails.caregivers.invite-new-user'
                : 'emails.caregivers.invite-existing-user',
            with: [
                'inviter' => $this->invite->inviter,
                'url' => $this->isNewUser
                    ? URL::signedRoute(
                        "invitation.accept",
                        ['invitation' => $this->invite]
                    )
                    : "test",
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
