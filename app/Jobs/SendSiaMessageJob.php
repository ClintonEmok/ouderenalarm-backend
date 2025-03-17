<?php

namespace App\Jobs;

use Crc16\Crc16;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSiaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;
    protected $port;
    protected $accountId;
    protected $eventCode;
    protected $data;
    protected $encrypt;

    private static $sequenceNumber = 1; // Start from 1 within script execution

    /**
     * Create a new job instance.
     */
    public function __construct($server, $port, $accountId, $eventCode, $data = '', $encrypt = false)
    {
        $this->server = $server;
        $this->port = $port;
        $this->accountId = $accountId;
        $this->eventCode = $eventCode;
        $this->data = $data;
        $this->encrypt = $encrypt;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $message = $this->encodeMessage($this->eventCode, $this->accountId, $this->data, $this->encrypt);
        $response = $this->sendMessage($message);

        // Log response
        Log::info("SIA Message Sent: " . bin2hex($message));
        Log::info("SIA Message Sent: " . $message);
        Log::info("Response: " . bin2hex($response));
    }

    /**∂
     * Encode a SIA message in binary format.
     */
    public function encodeMessage(string $eventCode, string $accountId, string $data, bool $encrypt = false): string
    {
        // Validate event code (2 uppercase letters)
        if (!preg_match('/^[A-Z]{2}$/', $eventCode)) {
            throw new \InvalidArgumentException('Invalid event code format. Must be two uppercase letters.');
        }

        // Validate account ID (alphanumeric)
        if (!preg_match('/^[A-Za-z0-9]+$/', $accountId)) {
            throw new \InvalidArgumentException('Invalid account ID format.');
        }

        // Generate sequence number (4 digits, padded if needed)
        $sequence = str_pad($this->getNextSequence(), 4, '0', STR_PAD_LEFT);

        // Create timestamp in required SIA format
        $timestamp = gmdate('_H:i:s,m-d-Y');

        // Message body inside brackets (starts with account ID, event code, data)
        $messageBody = "#{$accountId}|{$eventCode}{$data}";

        // Full message content (including quotes around SIA-DCS)
        $messageContent = "\"SIA-DCS\"{$sequence}L0#{$accountId}[{$messageBody}]";

        // Calculate CRC for the message content
        $result = Crc16::IBM($messageContent);
        $crcHex = dechex($result);



        // Calculate message length in hex (length of messageContent only)
        $length = strlen($messageContent);
        $lengthHex = strtoupper(str_pad(dechex($length), 4, '0', STR_PAD_LEFT));

        // Assemble full message: <LF> <CRC><LENGTH><messageContent><CR>
        $finalMessage = chr(0x0A) . "{$crcHex}{$lengthHex}{$messageContent}" . chr(0x0D);

        // Encrypt if requested
        return $encrypt ? $this->encryptMessage($finalMessage) : $finalMessage;
    }

    /**
     * Get the next sequence number, resetting after 9999.
     */
    private function getNextSequence(): string
    {
        $sequence = str_pad(self::$sequenceNumber, 4, '0', STR_PAD_LEFT);
        self::$sequenceNumber = (self::$sequenceNumber % 9999) + 1;
        return $sequence;
    }

    /**
     * Calculate CRC checksum for the message.
     */
    private function calculateCRC($data)
    {
        $crc = 0x0000;
        $poly = 0x8005;

        $bytes = unpack('C*', $data);

        foreach ($bytes as $byte) {
            $crc ^= $byte;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x0001) {
                    $crc = ($crc >> 1) ^ $poly;
                } else {
                    $crc >>= 1;
                }
            }
            $crc &= 0xFFFF; // Keep it 16-bit
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Encrypt a SIA message using AES-256-CBC.
     */
    private function encryptMessage(string $message): string
    {
        $key = env('SIA_AES_KEY', 'fallback-default-key');
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($message, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return bin2hex($iv . $encrypted);
    }

    /**
     * Send the message via TCP in binary mode.
     */
    private function sendMessage(string $message, string $protocol = 'tcp')
    {
        Log::info("Server {$this->server}");
        Log::info("Port {$this->port}");
        Log::info("Protocol {$protocol}");

        if ($protocol === 'udp') {
            $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        } elseif ($protocol === 'tcp') {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        } else {
            Log::error("Invalid protocol specified: {$protocol}");
            return false;
        }

        if (!$socket) {
            Log::error("Unable to create socket: " . socket_strerror(socket_last_error()));
            return false;
        }

        if ($protocol === 'tcp') {
            // For TCP, you need to connect first
            if (!socket_connect($socket, $this->server, $this->port)) {
                Log::error("Unable to connect TCP socket: " . socket_strerror(socket_last_error($socket)));
                socket_close($socket);
                return false;
            }

            // Send TCP message
            $sent = socket_write($socket, $message, strlen($message));
            if ($sent === false) {
                Log::error("Failed to send TCP message: " . socket_strerror(socket_last_error($socket)));
                socket_close($socket);
                return false;
            }
        } else {
            // Send UDP message
            $sent = socket_sendto($socket, $message, strlen($message), 0, $this->server, $this->port);
            if ($sent === false) {
                Log::error("Failed to send UDP message: " . socket_strerror(socket_last_error($socket)));
                socket_close($socket);
                return false;
            }
        }

        // Set timeout
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);

        // Receive response
        $response = '';
        $bytesReceived = false;

        if ($protocol === 'tcp') {
            $response = socket_read($socket, 1024, PHP_BINARY_READ);
            if ($response === false) {
                Log::warning("No response received or timeout reached on TCP.");
            }
        } else {
            $from = '';
            $port = 0;
            $bytesReceived = socket_recvfrom($socket, $response, 1024, 0, $from, $port);
            if ($bytesReceived === false) {
                Log::warning("No response received or timeout reached on UDP.");
                $response = false;
            }
        }

        socket_close($socket);

        return $response;
    }
}
