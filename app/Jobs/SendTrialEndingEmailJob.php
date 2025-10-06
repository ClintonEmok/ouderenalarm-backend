<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\TrialEndingSoonMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTrialEndingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            // Gebruiker bestaat niet meer — niets doen
            return;
        }

        // Verstuur herinnering per e-mail over einde proefperiode
        Mail::to($user->email)->send(new TrialEndingSoonMail($user));
    }
}