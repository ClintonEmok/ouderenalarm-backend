<?php

namespace App\Console\Commands;

use App\Jobs\SendSiaMessageJob;
use App\Services\SiaEncoderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTestSIAMessage extends Command
{
    protected $signature = 'sia:test-message';
    protected $description = 'Send a test SIA message to the monitoring server';

    public function handle()
    {
//        $encoder = new SiaEncoderService();
//
//        $eventCode = 'QA'; // 'RX' is often used for manual test reports
//        $accountId = 3203; // Replace with your test account ID
//        $extraInfo = 'https://ouderen-alarmering.nl/'; // Test URL
//
//        $encryptedMessage = $encoder->encodeMessage($eventCode, $accountId, $extraInfo);
//
//        // Send to monitoring server
//        $this->sendToMonitoringServer($encryptedMessage);
//
//        Log::info("Test SIA message sent for account {$accountId}");
        $server = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');
        $account = "3203";
        $eventCode = "NQA";

        SendSiaMessageJob::dispatch($server, $port, $account, $eventCode);
    }

    private function sendToMonitoringServer(string $message)
    {
        $host = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');

        Log::info("Connecting to: {$host}:{$port}");

        // Ensure message is correctly formatted according to SIA DC-09
        $formattedMessage = $message . "\r\n"; // DC-09 messages often require CRLF (\r\n)

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket || !socket_connect($socket, $host, $port)) {
            Log::error('SIA message send failed: ' . socket_strerror(socket_last_error()));
            return;
        }

        Log::info("Sending SIA DC-09 message: " . $formattedMessage);
        socket_write($socket, $formattedMessage, strlen($formattedMessage));

        // Read response if needed
        $response = socket_read($socket, 2048);
        Log::info("Monitoring server response: {$response}");

        socket_close($socket);
    }
}
