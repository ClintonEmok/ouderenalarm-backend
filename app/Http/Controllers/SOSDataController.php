<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Device;

class SOSDataController extends Controller
{
    /**
     * Handle incoming device messages.
     */
    public function receiveData(Request $request)
    {
        $rawData = $request->input('data');
        $hexData = bin2hex($rawData);  // Convert binary to hex for logging/debugging
        Log::info("Received raw data: " . $hexData);

        // Parse message according to documentation
        $header = substr($hexData, 0, 2); // Should be "AB"
        $properties = substr($hexData, 2, 2);
        $length = hexdec(substr($hexData, 4, 4));  // Length of message body
        $checksum = substr($hexData, 8, 4);  // Checksum for validation
        $sequenceId = substr($hexData, 12, 4);  // Sequence ID
        $body = substr($hexData, 16);  // Message body

        if ($header !== "ab") {
            return response()->json(['error' => 'Invalid header'], 400);
        }

        // Calculate CRC16 checksum for validation
        $calculatedCrc = $this->calculateCRC(substr($hexData, 16));
        if (strtoupper($checksum) !== strtoupper($calculatedCrc)) {
            Log::warning("Invalid checksum. Expected: $checksum, Calculated: $calculatedCrc");
            return response()->json(['error' => 'Checksum mismatch'], 400);
        }

        // Parse command and key-value pairs
        $command = substr($body, 0, 2);  // 1st byte of body
        $deviceIdKey = substr($body, 2, 2);
        $deviceIdLength = hexdec(substr($body, 4, 2));
        $deviceId = hex2bin(substr($body, 6, $deviceIdLength * 2));  // Device IMEI

        $device = Device::where('imei', $deviceId)->first();
        if (!$device) {
            Log::error("Device with IMEI $deviceId not found");
            return response()->json(['error' => 'Device not registered'], 404);
        }

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
        if (hexdec($properties) & 0x10) {  // ACK flag check
            $ackResponse = $this->generateACK($sequenceId);
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
    private function calculateCRC($data)
    {
        $crc = 0xFFFF;  // Initial CRC value
        $polynomial = 0x1021;  // Standard CRC16 polynomial

        for ($i = 0; $i < strlen($data); $i += 2) {
            $byte = hexdec(substr($data, $i, 2));
            $crc ^= ($byte << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ $polynomial;
                } else {
                    $crc <<= 1;
                }
            }
            $crc &= 0xFFFF;  // Mask to 16 bits
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
