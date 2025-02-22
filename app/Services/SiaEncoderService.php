<?php

namespace App\Services;

class SIAEncoder
{
    private $currentSequence = 1;

    /**
     * Encode a SIA message.
     */
    public function encodeMessage(string $eventCode, string $accountId, string $data): string
    {
        if (!preg_match('/^[A-Z]{2}$/', $eventCode)) {
            throw new \InvalidArgumentException('Invalid event code format.');
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $accountId)) {
            throw new \InvalidArgumentException('Invalid account ID format.');
        }

        $sequence = $this->getNextSequence();
        $timestamp = gmdate('_H:i:s,m-d-Y');
        $messageData = "#{$accountId}|{$eventCode}|{$data}";
        $crc = $this->calculateCRC($messageData);

        $message = "<LF>{$crc}<0LLL>*SIA-DCS{$sequence}R0000L0000#{$accountId}[{$this->padData($messageData)}]{$timestamp}<CR>";
        return $this->encryptMessage($message);
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
     * Pad data to ensure it is a multiple of 16 bytes.
     */
    private function padData(string $data): string
    {
        $padLength = 16 - (strlen($data) % 16);
        $padData = '';
        for ($i = 0; $i < $padLength; $i++) {
            $char = chr(random_int(0, 255));
            // Exclude specific ASCII characters
            if (!in_array($char, ['|', '[', ']'])) {
                $padData .= $char;
            }
        }
        return $padData . '|'; // Add pad termination character
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
        $type = substr($response, 0, 3); // Extract ACK type
        $sequence = substr($response, 3, 4); // Extract sequence number
        $timestamp = substr($response, -20); // Extract timestamp if applicable

        return [
            'type' => $type,
            'sequence' => $sequence,
            'timestamp' => $timestamp,
        ];
    }
}

