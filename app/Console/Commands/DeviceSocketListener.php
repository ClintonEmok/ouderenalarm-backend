<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SOSDataController;

class DeviceSocketListener extends Command
{
    protected $signature = 'app:device-socket-listener {port=5050}';
    protected $description = 'Listen for incoming EV-07B device data via TCP connection';

    public function handle()
    {
        $port = $this->argument('port');
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$socket) {
            $this->error("Failed to create socket: " . socket_strerror(socket_last_error()));
            return;
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (!socket_bind($socket, '0.0.0.0', $port)) {
            $this->error("Failed to bind to port {$port}: " . socket_strerror(socket_last_error($socket)));
            socket_close($socket);
            return;
        }

        if (!socket_listen($socket)) {
            $this->error("Failed to listen on port {$port}: " . socket_strerror(socket_last_error($socket)));
            socket_close($socket);
            return;
        }

        $this->info("Listening for incoming device data on port {$port}...");

        while (true) {
            $client = @socket_accept($socket);

            if ($client === false) {
                Log::error("Failed to accept connection: " . socket_strerror(socket_last_error($socket)));
                continue;
            }

            // ✅ Set 5-second receive timeout on client socket
            socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 30, 'usec' => 0]);

            Log::info("New connection established");

            $this->handleClient($client);
        }

        socket_close($socket);
    }

    private function handleClient($client)
    {
        try {
            while (true) {
                $data = socket_read($client, 2048);

                if ($data === false || empty($data)) {
                    Log::warning("Device disconnected, timed out, or sent empty data.");
                    break;
                }

                $sosDataController = new SOSDataController();
                $request = new \Illuminate\Http\Request(['data' => $data]);
                $response = $sosDataController->receiveData($request);

                if (!empty($response)) {
                    Log::info("HEX Response Before Sending: " . $response);

                    $ackResponse = pack('H*', $response);
                    $bytesWritten = socket_write($client, $ackResponse, strlen($ackResponse));

                    if ($bytesWritten === false) {
                        Log::error("Failed to send ACK to device: " . socket_strerror(socket_last_error($client)));
                    } else {
                        Log::info("ACK successfully sent $ackResponse | Bytes written: $bytesWritten");
                    }
                }

                break;
            }
        } catch (\Exception $e) {
            Log::error("Error handling device connection: " . $e->getMessage());
        } finally {
            socket_close($client);
            Log::info("Client connection closed.");
        }
    }
}