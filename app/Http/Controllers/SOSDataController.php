<?php

namespace App\Http\Controllers;

use App\Models\DeviceAlarm;
use App\Models\GPSLocation;
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
        try {
            $rawData = $request->input('data');  // Binary data from the device
            if (!$rawData) {
                return response()->json(['error' => 'No data provided'], 400);
            }

            $hexData = bin2hex($rawData);  // Convert binary to hex for logging/debugging
            Log::info("Received raw data: " . $hexData);

            if (strlen($hexData) < 16) {
                return response()->json(['error' => 'Message too short'], 400);
            }

            $header = mb_substr($hexData, 0, 2);  // Should be "AB"
            $properties = mb_substr($hexData, 2, 2);
            $lengthHex = mb_substr($hexData, 4, 4);
            $length = hexdec(mb_substr($lengthHex, 2, 2) . mb_substr($lengthHex, 0, 2));
            $checksum = mb_substr($hexData, 8, 4);  // Checksum for validation
            $sequenceId = mb_substr($hexData, 12, 4);  // Sequence ID
            $body = mb_substr($hexData, 16);  // Message body starts after 16 hex characters (8 bytes)

            if (strtolower($header) !== "ab") {
                Log::error("Invalid header: $header");
                return response()->json(['error' => 'Invalid header'], 400);
            }

            if ($length !== strlen($body) / 2) {
                Log::error("Invalid body length: Expected $length, Actual " . strlen($body) / 2);
                return response()->json(['error' => 'Length mismatch'], 400);
            }

            $calculatedCrc = $this->calculateCRC(hex2bin(mb_substr($hexData, 16)));  // Calculate CRC for body
            $calculatedCrcSwapped = bin2hex(strrev(hex2bin($calculatedCrc)));  // Swap bytes for little-endian format

            Log::info("Calculated CRC: $calculatedCrcSwapped");
            Log::info("Received checksum: $checksum");
            if (strtoupper($checksum) !== strtoupper($calculatedCrcSwapped)) {
                Log::warning("Invalid checksum. Expected: $checksum, Calculated: $calculatedCrcSwapped");
                return response()->json(['error' => 'Checksum mismatch'], 400);
            }

            // Extract IMEI (first key in the body)
            $command = mb_substr($body, 0, 2);  // Command (1 byte)
            $deviceIdLength = hexdec(mb_substr($body, 2, 2));  // Length of the IMEI
            $deviceIdKey = mb_substr($body, 4, 2);  // Key indicating IMEI (should be 01 for IMEI)
            $imeiHex = mb_substr($body, 6, $deviceIdLength * 2);  // IMEI hex string
            $deviceImei = $this->parseHexToAscii($imeiHex);  // Convert IMEI to ASCII
            Log::info("Extracted IMEI: $deviceImei");

            $device = Device::firstOrCreate(['imei' => $deviceImei]);
            Log::info("Device found or created: {$device->imei}");

            // Iterate over the remaining keys
            $offset = 4 + $deviceIdLength * 2;  // Start after IMEI key-value pair
            while ($offset < strlen($body)) {
                Log::info("Current Offset: $offset, Next Key-Value Hex: " . mb_substr($body, $offset, 10));
                $keyLength = hexdec(mb_substr($body, $offset, 2));  // Length of the key value
                $key = mb_substr($body, $offset + 2, 2);  // Key ID
                $value = $keyLength > 1 ? mb_substr($body, $offset + 4, $keyLength * 2) : null;  // Key value or NULL if length is 1

                Log::info("Key: $key, Length: $keyLength, Value: " . ($value ?? 'NULL'));

                // Handle keys based on their types
                switch ($key) {
                    case '01':  // IMEI (already handled)
                        Log::info("IMEI already processed.");
                        break;
                    case '02':  // Alarm Code
                        $this->handleAlarmCode($device, $value);
                        break;
                    case '20':  // GPS Location
                        $this->handleGPSLocation($device, $value);
                        break;
                    case '24':  // General Status
                        $this->handleGeneralStatus($device, $value);
                        break;
                    default:
                        Log::warning("Unknown key received: $key");
                        break;
                }

                // Move to the next key-value pair
                $offset += 4 + ($keyLength * 2);  // 4 bytes for key length + key + value length
            }

            if (hexdec($properties) & 0x10) {  // Check ACK flag
                $ackResponse = $this->generateACK($sequenceId);
                Log::info("Sending ACK response...");
                return response($ackResponse, 200)->header('Content-Type', 'application/octet-stream');
            }

            return response()->json(['message' => 'Data processed successfully']);
        } catch (\Exception $e) {
            Log::error("Exception occurred: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Handle GPS Location (Key 0x20).
     */
    private function handleGPSLocation($body, $deviceId)
    {
        // Parsing the GPS location data
        $latitude = $this->parseSignedDecimal(mb_substr($body, 6, 8));  // 4 bytes for latitude
        $longitude = $this->parseSignedDecimal(mb_substr($body, 14, 8));  // 4 bytes for longitude
        $speed = hexdec(mb_substr($body, 22, 4));  // 2 bytes for speed in KM/H
        $direction = hexdec(mb_substr($body, 26, 4));  // 2 bytes for direction in degrees
        $altitude = hexdec(mb_substr($body, 30, 4));  // 2 bytes for altitude in meters

        // Additional data if needed
        $accuracy = hexdec(mb_substr($body, 34, 4)) / 10;  // Horizontal positioning accuracy
        $mileage = hexdec(mb_substr($body, 38, 8));  // 4 bytes for mileage in meters
        $satellites = hexdec(mb_substr($body, 46, 2));  // 1 byte for number of satellites

        // Logging for debugging
        Log::info("GPS Location: Lat: $latitude, Lon: $longitude, Speed: $speed km/h, Direction: $direction, Altitude: $altitude m, Satellites: $satellites");

        // Store GPS location in the database
        GPSLocation::create([
            'device_id' => $deviceId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed' => $speed,
            'direction' => $direction,
            'altitude' => $altitude,
            'horizontal_accuracy' => $accuracy,
            'mileage' => $mileage,
            'satellites' => $satellites,
        ]);
    }

    /**
     * Handle Alarm Code (Key 0x02).
     */
    private function handleAlarmCode($device, $value)
    {
        $timestampHex = mb_substr($value, 0, 8);
        $timestamp = hexdec($timestampHex);
        $alarmDetails = mb_substr($value, 8);

        $alarmBinary = str_pad(base_convert($alarmDetails, 16, 2), 32, '0', STR_PAD_LEFT);

        $alarmData = [
            'device_id' => $device->id,
            'triggered_at' => $timestamp === 0 ? now() : gmdate("Y-m-d H:i:s", $timestamp),
            'battery_low_alert' => $alarmBinary[31 - 0] === '1',
            'over_speed_alert' => $alarmBinary[31 - 1] === '1',
            'fall_down_alert' => $alarmBinary[31 - 2] === '1',
            'welfare_alert' => $alarmBinary[31 - 3] === '1',
            'geo_1_alert' => $alarmBinary[31 - 4] === '1',
            'geo_2_alert' => $alarmBinary[31 - 5] === '1',
            'geo_3_alert' => $alarmBinary[31 - 6] === '1',
            'geo_4_alert' => $alarmBinary[31 - 7] === '1',
            'power_off_alert' => $alarmBinary[31 - 8] === '1',
            'power_on_alert' => $alarmBinary[31 - 9] === '1',
            'motion_alert' => $alarmBinary[31 - 10] === '1',
            'no_motion_alert' => $alarmBinary[31 - 11] === '1',
            'sos_alert' => $alarmBinary[31 - 12] === '1',
            'side_call_button_1' => $alarmBinary[31 - 13] === '1',
            'side_call_button_2' => $alarmBinary[31 - 14] === '1',
            'battery_charging_start' => $alarmBinary[31 - 15] === '1',
            'no_charging' => $alarmBinary[31 - 16] === '1',
            'sos_ending' => $alarmBinary[31 - 17] === '1',
            'amber_alert' => $alarmBinary[31 - 18] === '1',
            'welfare_alert_ending' => $alarmBinary[31 - 19] === '1',
            'fall_down_ending' => $alarmBinary[31 - 20] === '1',
            'one_day_upload' => $alarmBinary[31 - 22] === '1',
            'beacon_absence' => $alarmBinary[31 - 23] === '1',
            'bark_detection' => $alarmBinary[31 - 24] === '1',
            'ble_disconnected' => $alarmBinary[31 - 30] === '1',
            'watch_taken_away' => $alarmBinary[31 - 31] === '1',
        ];

        // Store in the database
        DeviceAlarm::create($alarmData);

        Log::info("Device {$device->imei} Alarm recorded: " . gmdate("Y-m-d H:i:s", $timestamp));
    }


    /**
     * Handle General Status (Key 0x24).
     */
    private function handleGeneralStatus($device, $value)
    {
        $timestamp = hexdec(mb_substr($value, 0, 8));
        $batteryLevel = hexdec(mb_substr($value, 16, 2));
        Log::info("Device {$device->imei} General Status: Battery: $batteryLevel%, Timestamp: " . gmdate("Y-m-d H:i:s", $timestamp));
    }

    /**
     * Handle Call Record (Key 0x25).
     */
    private function handleCallRecord($device, $value)
    {
        $timestamp = hexdec(mb_substr($value, 0, 8));
        $callStatus = mb_substr($value, 8, 2);
        $phoneNumber = $this->parsePhoneNumber(mb_substr($value, 10));
        Log::info("Device {$device->imei} Call Record: Call Status: $callStatus, Phone Number: $phoneNumber, Timestamp: " . gmdate("Y-m-d H:i:s", $timestamp));
    }

    /**
     * Generate ACK response.
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
     * Convert hex to ASCII string.
     */
    private function parseHexToAscii($hexString)
    {
        $ascii = '';
        for ($i = 0; $i < strlen($hexString); $i += 2) {
            $ascii .= chr(hexdec(mb_substr($hexString, $i, 2)));
        }
        return $ascii;
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

    /**
     * Calculate CRC16 checksum for message body.
     */
    private function calculateCRC($data)
    {
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
            $asciiChar = chr(hexdec(mb_substr($hexString, $i, 2)));
            $phoneNumber .= $asciiChar;
        }
        return $phoneNumber;
    }
}
