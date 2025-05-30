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
        $port = $this->argument('port');  // Get port from command argument
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);  // Create a TCP socket

        if (!$socket) {
            $this->error("Failed to create socket: " . socket_strerror(socket_last_error()));
            return;
        }

        // Allow immediate rebinding of the port if the process restarts
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        // Bind the socket to the specified port
        if (!socket_bind($socket, '0.0.0.0', $port)) {
            $this->error("Failed to bind to port {$port}: " . socket_strerror(socket_last_error($socket)));
            socket_close($socket);
            return;
        }

        // Start listening for connections
        if (!socket_listen($socket)) {
            $this->error("Failed to listen on port {$port}: " . socket_strerror(socket_last_error($socket)));
            socket_close($socket);
            return;
        }

        $this->info("Listening for incoming device data on port {$port}...");

        // Main loop to accept multiple client connections
        while (true) {
            $client = @socket_accept($socket);  // Accept an incoming connection

            if ($client === false) {
                Log::error("Failed to accept connection: " . socket_strerror(socket_last_error($socket)));
                continue;
            }

            Log::info("New connection established");

            // Handle client communication in a separate process/thread (if needed)
            $this->handleClient($client);
        }

        // Close the main server socket when exiting
        socket_close($socket);
    }

    /**
     * Handles an individual device connection.
     */
    private function handleClient($client)
    {
        try {
            while (true) {
                // Read data from the device
                $data = socket_read($client, 2048);

                if ($data === false || empty($data)) {
                    Log::warning("Device disconnected or sent empty data.");
                    break;  // Exit the loop and close the connection
                }

                // Process data using SOSDataController
                $sosDataController = new SOSDataController();
                $request = new \Illuminate\Http\Request(['data' => $data]);
                $response = $sosDataController->receiveData($request);

                // Ensure response is valid before sending ACK
                if (!empty($response)) {
                    Log::info("HEX Response Before Sending: " . $response);

                    // Convert HEX-encoded string to raw binary string
                    $ackResponse = pack('H*', $response);

                    // Send ACK as a raw string
                    $bytesWritten = socket_write($client, $ackResponse, strlen($ackResponse));

                    if ($bytesWritten === false) {
                        Log::error("Failed to send ACK to device: " . socket_strerror(socket_last_error($client)));
                    } else {
                        Log::info("ACK successfully sent $ackResponse | Bytes written: $bytesWritten");
                    }
                }

                // Close the socket immediately after sending ACK
                break;
            }
        } catch (\Exception $e) {
            Log::error("Error handling device connection: " . $e->getMessage());
        } finally {
            // Close the client socket connection
            socket_close($client);
            Log::info("Client connection closed.");
        }
    }
}
