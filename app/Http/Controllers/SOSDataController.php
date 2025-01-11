<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Device;

//TODO: handle extra cases
class SOSDataController extends Controller
{
    /**
     * Handle incoming device messages.
     */
    public function receiveData(Request $request)
    {
        $rawData = $request->input('data');  // Binary data from the device
        $hexData = bin2hex($rawData);  // Convert binary to hex for logging/debugging
        Log::info("Received raw data: " . $hexData);

        // Parse message components
        $header = substr($hexData, 0, 2); // Should be "AB"
        $properties = substr($hexData, 2, 2);
        $length = hexdec(substr($hexData, 4, 4));  // Length of message body
        $checksum = substr($hexData, 8, 4);  // Checksum for validation
        $sequenceId = substr($hexData, 12, 4);  // Sequence ID
        $body = substr($hexData, 16);  // Message body starts after 16 hex characters (8 bytes)

        // Validate header
        if ($header !== "ab") {
            Log::error("Invalid header: $header");
            return response()->json(['error' => 'Invalid header'], 400);
        }

        // Calculate CRC16 checksum for validation (calculate from sequence ID onwards)
        $crcBody = substr($hexData, 12);  // Sequence ID to the end
        $calculatedCrc = $this->calculateCRC(hex2bin($crcBody));

        // Swap the bytes of the calculated CRC to match little-endian format
        $calculatedCrcSwapped = substr($calculatedCrc, 2, 2) . substr($calculatedCrc, 0, 2);

        if (strtoupper($checksum) !== strtoupper($calculatedCrcSwapped)) {
            Log::warning("Invalid checksum. Expected: $checksum, Calculated: $calculatedCrcSwapped");
            return response()->json(['error' => 'Checksum mismatch'], 400);
        }

        // Parse the IMEI
        $command = substr($body, 0, 2);  // Command (1st byte of the body)
        $deviceIdLength = hexdec(substr($body, 2, 2));  // Key indicating IMEI
        $deviceIdKey = substr($body, 4, 2);  // Length of the IMEI (in bytes)
        Log::info("Device ID key: $deviceIdKey, length: $deviceIdLength");
        $imeiHex = substr($body, 6, $deviceIdLength * 2);  // IMEI hex string starts at offset 6
        Log::info("IMEI hex data: " . $imeiHex);

        $deviceImei = '';  // Initialize empty string for IMEI

        // Convert hex IMEI to ASCII characters
        for ($i = 0; $i < strlen($imeiHex); $i += 2) {
            $deviceImei .= chr(hexdec(substr($imeiHex, $i, 2)));  // Convert each hex pair to ASCII
        }

        Log::info("Extracted IMEI: " . $deviceImei);


        // Check if the device exists, if not, create it
        $device = Device::firstOrCreate(
            ['imei' => $deviceImei],
        );

        Log::info("Device found or created: {$device->imei}");

        // Handle different commands based on command code
        switch ($command) {
            case '20':  // GPS Location
                $this->handleGPSLocation($device, $body);
                break;
            case '24':  // General Status
                $this->handleGeneralStatus($device, $body);
                break;
            case '25':  // Call Records
                $this->handleCallRecord($device, $body);
                break;
            default:
                Log::warning("Unknown command received: $command");
                return response()->json(['message' => 'Unknown command'], 400);
        }

        // Send ACK if requested
        if (hexdec($properties) & 0x10) {  // Check ACK flag
            $ackResponse = $this->generateACK($sequenceId);
            Log::info("Sending ACK response...");
            return response($ackResponse, 200)->header('Content-Type', 'application/octet-stream');
        }

        return response()->json(['message' => 'Data processed successfully']);
    }

    /**
     * Handle GPS Location command (0x20).
     */
    private function handleGPSLocation($device, $body)
    {
        $latitude = $this->parseSignedDecimal(substr($body, 6, 8));
        $longitude = $this->parseSignedDecimal(substr($body, 14, 8));
        $speed = hexdec(substr($body, 22, 4));  // Speed in KM/H
        $direction = hexdec(substr($body, 26, 4));  // Direction in degrees
        $altitude = hexdec(substr($body, 30, 4));  // Altitude in meters

        Log::info("Device {$device->imei} GPS Location: Lat: $latitude, Lon: $longitude, Speed: $speed km/h, Altitude: $altitude m");
    }

    /**
     * Handle General Status command (0x24).
     */
    private function handleGeneralStatus($device, $body)
    {
        $timestamp = hexdec(substr($body, 6, 8));
        $batteryLevel = hexdec(substr($body, 22, 2));
        Log::info("Device {$device->imei} General Status: Battery: $batteryLevel%, Timestamp: $timestamp");
    }

    /**
     * Handle Call Record command (0x25).
     */
    private function handleCallRecord($device, $body)
    {
        $timestamp = hexdec(substr($body, 6, 8));
        $callStatus = substr($body, 14, 2);
        $phoneNumber = $this->parsePhoneNumber(substr($body, 16));
        Log::info("Device {$device->imei} Call Record: Call Status: $callStatus, Phone Number: $phoneNumber");
    }

    /**
     * Generate ACK response for received data.
     */
    private function generateACK($sequenceId)
    {
        $header = "ab";
        $properties = "00";  // No encryption, no ACK request
        $length = "0003";  // 3 bytes for the body
        $crc = $this->calculateCRC("7f0100");  // Negative response body
        $message = $header . $properties . $length . $crc . $sequenceId . "7f0100";
        return hex2bin($message);
    }

    /**
     * Calculate CRC16 checksum for message body.
     */
    private function calculateCRC($data) {
        $crc = 0x0000;  // Initial CRC value
        $size = strlen($data);  // Size of the data in bytes

        for ($i = 0; $i < $size; $i++) {
            $byte = ord($data[$i]);  // Convert character to byte (ASCII value)
            $crc = (($crc >> 8) & 0xFF) | (($crc & 0xFF) << 8);
            $crc ^= $byte;
            $crc ^= ($crc & 0xFF) >> 4;
            $crc ^= ($crc << 8) << 4;
            $crc ^= (($crc & 0xFF) << 4) << 1;
            $crc &= 0xFFFF;  // Keep only 16 bits
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Parse phone number from hex to readable format.
     */
    private function parsePhoneNumber($hexString)
    {
        $phoneNumber = '';
        for ($i = 0; $i < strlen($hexString); $i += 2) {
            $asciiChar = chr(hexdec(substr($hexString, $i, 2)));
            $phoneNumber .= $asciiChar;
        }
        return $phoneNumber;
    }

    /**
     * Parse signed decimal value from hex.
     */
    private function parseSignedDecimal($hexString)
    {
        $int = hexdec($hexString);
        if ($int >= 0x80000000) {
            $int -= 0x100000000;
        }
        return $int / 10000000.0;  // Convert to decimal degrees
    }
}
