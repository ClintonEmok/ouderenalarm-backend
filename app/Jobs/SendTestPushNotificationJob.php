<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;

class SendTestPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $token
    ) {}

    public function handle(Messaging $messaging)
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create('🚀 Test Push', 'Your push notification setup works!'))
            ->withData(['test' => 'true'])
            ->toToken($this->token);

        try {
            $messaging->send($message);
            Log::info("✅ Test push notification sent to token: {$this->token}");
        } catch (\Throwable $e) {
            Log::error("❌ Failed to send test push", [
                'token' => $this->token,
                'error' => $e->getMessage()
            ]);
        }
    }
}
