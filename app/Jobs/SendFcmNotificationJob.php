<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $payload = [
            'to' => $this->token,
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
            ],
            'data' => $this->data,
        ];

        $response = Http::withToken(config('services.fcm.server_key'))
            ->post('https://fcm.googleapis.com/fcm/send', $payload);

        if ($response->failed()) {
            logger()->error('FCM Push failed', [
                'token' => $this->token,
                'response' => $response->body(),
            ]);
        }
    }

    public function middleware(): array
    {
        return [
            // Optional: prevent overloads or quota exhaustion
            new RateLimited('fcm'),
        ];
    }
}