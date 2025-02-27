<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSiaMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $host = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket || !socket_connect($socket, $host, $port)) {
            Log::error('SIA message send failed: ' . socket_strerror(socket_last_error()));
            return;
        }

        socket_write($socket, $this->message, strlen($this->message));
        socket_close($socket);

        Log::info("Successfully sent SIA message: {$this->message}");
    }
}
