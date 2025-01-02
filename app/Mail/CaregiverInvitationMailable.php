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
    public $email;

    /**
     * Create a new message instance.
     *
     * @param string $token
     * @param string $patientName
     * @param string $email
     */
    public function __construct($token, $patientName, $email)
    {
        $this->token = $token;
        $this->patientName = $patientName;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $queryParams = http_build_query([
            'token' => $this->token,
            'email' => $this->email,
        ]);

        $url = config('app.frontend_url') . '/caregivers/accept?' . $queryParams;

        return $this->subject('Caregiver Invitation')
            ->view('emails.caregiver-invitation', [
                'url' => $url,
                'patientName' => $this->patientName,
            ]);
    }
}
