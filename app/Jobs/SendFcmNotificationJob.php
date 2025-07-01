<?php

namespace App\Jobs;

use App\Models\PushToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(Messaging $messaging): void
    {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($this->title, $this->body))
            ->withData($this->data)
            ->toToken($this->token);

        try {
            $messaging->send($message);
        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            // Token is invalid or expired
            PushToken::where('token', $this->token)->delete();
            Log::info("Deleted invalid push token: {$this->token}");
        } catch (\Throwable $e) {
            Log::error('Failed to send FCM push', [
                'token' => $this->token,
                'error' => $e->getMessage(),
            ]);
        }
    }
}