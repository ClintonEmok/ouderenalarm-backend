<?php


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCancellationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $connectionInfo;

    public function __construct(array $connectionInfo)
    {
        $this->connectionInfo = $connectionInfo;
    }

    public function handle(): void
    {
        $lines = array_map(function ($connectionNumber, $name) {
            return "{$connectionNumber} - {$name} (opzegging per direct)";
        }, array_keys($this->connectionInfo), $this->connectionInfo);

        $body = implode("\n", $lines);

        Mail::raw($body, function ($message) {
            $message->to('administratie@alarmmeldnet.nl')
                ->subject('Opzegging aansluitingen');
        });
    }
}