<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaregiverInvitationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $patientName;

    public function __construct($token, $patientName)
    {
        $this->token = $token;
        $this->patientName = $patientName;
    }

    public function build()
    {
        $url = config('app.url') . '/caregiver/accept?token=' . $this->token;

        return $this->subject('Caregiver Invitation')
            ->view('emails.caregiver-invitation', [
                'url' => $url,
                'patientName' => $this->patientName,
            ]);
    }
}
