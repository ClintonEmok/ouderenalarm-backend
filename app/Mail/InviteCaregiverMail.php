<?php

namespace App\Mail;

use App\Models\Invite;
use App\Models\User;
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
        $inviterName = User::find($this->invite->inviter_id)?->name ?? 'iemand';

        return new Envelope(
            subject: $this->isNewUser
                ? "Wil je bevestigen dat je mantelzorger wordt voor {$inviterName}?"
                : "{$inviterName} heeft je uitgenodigd als mantelzorger.",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
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
