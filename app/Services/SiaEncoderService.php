<?php

namespace App\Services;

class SiaEncoderService
{
    private $currentSequence = 1;

    /**
     * Encode a SIA message.
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

        // Prepare message body (inside the square brackets)
        $messageBody = "#{$accountId}|{$eventCode}|{$data}";

        // Assemble full message content (excluding LF, CRC, and CR)
        $messageContent = "SIA-DCS{$sequence}R0000L0000#{$accountId}[{$messageBody}]{$timestamp}";

        // Calculate CRC for the message content
        $crc = $this->calculateCRC($messageContent);

        // Calculate message length in hex (length of messageContent only)
        $length = strlen($messageContent);
        $lengthHex = strtoupper(str_pad(dechex($length), 4, '0', STR_PAD_LEFT));

        // Assemble full message: <LF> <CRC><LENGTH><messageContent><CR>
        $finalMessage = chr(0x0A) . "{$crc}{$lengthHex}{$messageContent}" . chr(0x0D);

        // Encrypt if requested
        return $encrypt ? $this->encryptMessage($finalMessage) : $finalMessage;
    }


    /**
     * Get the next sequence number, resetting after 9999.
     */
    private function getNextSequence(): string
    {
        $sequence = str_pad($this->currentSequence, 4, '0', STR_PAD_LEFT);
        $this->currentSequence = ($this->currentSequence % 9999) + 1;
        return $sequence;
    }

    /**
     * Calculate CRC checksum for the message.
     */
    private function calculateCRC(string $message): string
    {
        $crc = 0;
        for ($i = 0; $i < strlen($message); $i++) {
            $crc += ord($message[$i]);
        }
        return strtoupper(str_pad(dechex($crc & 0xFFFF), 4, '0', STR_PAD_LEFT));
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
     * Decrypt a SIA message.
     */
    public function decryptMessage(string $encryptedHex): string
    {
        $key = env('SIA_AES_KEY', 'fallback-default-key');
        $binaryData = hex2bin($encryptedHex);
        $iv = substr($binaryData, 0, 16);
        $encrypted = substr($binaryData, 16);

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Handle supervision (Null Message).
     */
    public function generateNullMessage(string $accountId): string
    {
        $sequence = $this->getNextSequence();
        $timestamp = gmdate('_H:i:s,m-d-Y');
        $crc = $this->calculateCRC("NULL{$accountId}");

        $message = "<LF>{$crc}<0LLL>*NULL{$sequence}R0000L0000#{$accountId}[]{$timestamp}<CR>";
        return $this->encryptMessage($message);
    }

    /**
     * Parse acknowledgment messages (ACK, NAK, DUH).
     */
    public function parseAcknowledgment(string $response): array
    {
        return [
            'type' => substr($response, 0, 3),
            'sequence' => substr($response, 3, 4),
            'timestamp' => substr($response, -20),
        ];
    }
}
