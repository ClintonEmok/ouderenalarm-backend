<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SOSDataController;

class DeviceSocketListener extends Command
{
    protected $signature = 'app:device-socket-listener {port=5050}';  // Command with default port 5050
    protected $description = 'Listen for incoming EV-07B device data via TCP connection';

    public function handle()
    {
        $port = $this->argument('port');  // Get the port from command argument
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);  // Create a TCP socket

        if (!$socket) {
            $this->error("Failed to create socket");
            return;
        }

        // Bind the socket to the specified port
        if (!socket_bind($socket, '0.0.0.0', $port)) {
            $this->error("Failed to bind to port {$port}");
            socket_close($socket);
            return;
        }

        socket_listen($socket);  // Start listening for connections
        $this->info("Listening for incoming device data on port {$port}...");

        while (true) {
            $client = socket_accept($socket);  // Accept an incoming connection
            if (!$client) {
                Log::error("Failed to accept connection");
                continue;
            }

            $data = socket_read($client, 2048);  // Read up to 2048 bytes from the device
            if (!$data) {
                Log::error("No data received");
                socket_close($client);
                continue;
            }

            $hexData = bin2hex($data);  // Convert binary data to hex for parsing
            Log::info("Received raw data: " . $hexData);

            // Pass data to SOSDataController for handling
            $sosDataController = new SOSDataController();
            $request = new \Illuminate\Http\Request(['data' => $data]);  // Wrap data as request

            $response = $sosDataController->receiveData($request);

            // Send acknowledgment if applicable
            if ($response->status() === 200 && $response->content()) {
                socket_write($client, $response->content());  // Send ACK or NAK response
                Log::info("Acknowledgment sent to device");
            }

            socket_close($client);  // Close the client connection after processing
        }

        socket_close($socket);  // Close the server socket
    }
}
