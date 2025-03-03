<?php

namespace App\Console\Commands;

use App\Services\SiaEncoderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTestSIAMessage extends Command
{
    protected $signature = 'sia:test-message';
    protected $description = 'Send a test SIA message to the monitoring server';

    public function handle()
    {
        $encoder = new SiaEncoderService();

        $eventCode = 'QA'; // 'RX' is often used for manual test reports
        $accountId = 3203; // Replace with your test account ID
        $extraInfo = 'https://ouderen-alarmering.nl/'; // Test URL

        $encryptedMessage = $encoder->encodeMessage($eventCode, $accountId, $extraInfo);

        // Send to monitoring server
        $this->sendToMonitoringServer($encryptedMessage);

        Log::info("Test SIA message sent for account {$accountId}");
    }

    private function sendToMonitoringServer(string $message)
    {
        $host = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');


        Log::info($host . ":" . $port);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket || !socket_connect($socket, $host, $port)) {
            Log::error('SIA test message send failed: ' . socket_strerror(socket_last_error()));
            return;
        }


        Log::info("Bericht". $message);
        socket_write($socket, $message, strlen($message));
//        $response = socket_read($socket, 2048);
        socket_close($socket);

//        Log::info("Monitoring server response: {$response}");
    }
}
