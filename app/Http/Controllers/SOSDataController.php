<?php

namespace App\Http\Controllers;

use App\Models\DeviceAlarm;
use App\Models\GeneralStatus;
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
            Log::info("Received raw data (converted to hex): " . $hexData);

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
//                return response()->json(['error' => 'Length mismatch'], 400);
            }

            $calculatedCrc = $this->calculateCRC(hex2bin(mb_substr($hexData, 16)));  // Calculate CRC for body
            $calculatedCrcSwapped = bin2hex(strrev(hex2bin($calculatedCrc)));  // Swap bytes for little-endian format

            Log::info("Calculated CRC: $calculatedCrcSwapped");
            Log::info("Received checksum: $checksum");
            if (strtoupper($checksum) !== strtoupper($calculatedCrcSwapped)) {
                Log::warning("Invalid checksum. Expected: $checksum, Calculated: $calculatedCrcSwapped");
//                return response()->json(['error' => 'Checksum mismatch'], 400);
            }

            // Extract IMEI (first key in the body)
            $command = mb_substr($body, 0, 2);  // Command (1 byte)
            $deviceIdLength = hexdec(mb_substr($body, 2, 2));  // Length of the IMEI
            $deviceIdKey = mb_substr($body, 4, 2);  // Key indicating IMEI (should be 01 for IMEI)
            $imeiHex = mb_substr($body, 6, $deviceIdLength * 2);  // IMEI hex string
            $deviceImei = $this->parseHexToAscii($imeiHex);  // Convert IMEI to ASCII
            Log::info("Extracted IMEI: $deviceImei");

            $device = Device::firstOrCreate(['imei' => trim($deviceImei)]);
            Log::info("Device found or created: {$device->imei}");

            // Iterate over the remaining keys
            $offset = 4 + $deviceIdLength * 2;  // Start after IMEI key-value pair
            while ($offset < strlen($body)) {
                Log::info("Current Offset: $offset, Next Key-Value Hex: " . mb_substr($body, $offset, 10));
                $keyLength = hexdec(mb_substr($body, $offset, 2));  // Length of the key value
                $key = mb_substr($body, $offset + 2, 2);  // Key ID
                $value = $keyLength > 1 ? mb_substr($body, $offset + 4, ($keyLength * 2)-2) : null;  // Key value or NULL if length is 1

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
//                TODO: update
                $offset += 2 + ($keyLength * 2);  // 4 bytes for key length + key + value length
            }

            if (hexdec($properties) & 0x10) {  // Check ACK flag
                $ackResponse = $this->generateACK($sequenceId);
                Log::info("Sending ACK response... " . $ackResponse);
                return strtoupper($ackResponse);
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
    private function handleGPSLocation($device, $body)
    {
        // Parsing the GPS location data

        $latitude = $this->littleEndianHexDec(mb_substr($body, 0, 8)) /  10000000.0;  // 4 bytes for latitude

        $longitude = $this->littleEndianHexDec(mb_substr($body, 8, 8)) / 10000000.0;  // 4 bytes for longitude

        $speed = $this->littleEndianHexDec(mb_substr($body, 16, 4));  // 2 bytes for speed in KM/H

        $direction = $this->littleEndianHexDec(mb_substr($body, 20, 4));  // 2 bytes for direction in degrees

        $altitude = $this->littleEndianHexDec(mb_substr($body, 24, 4));  // 2 bytes for altitude in meters

        $accuracy = $this->littleEndianHexDec(mb_substr($body, 28, 4));  // Horizontal positioning accuracy

        $mileage = $this->littleEndianHexDec(mb_substr($body, 32, 8));  // 4 bytes for mileage in meters

        $satellites = $this->littleEndianHexDec(mb_substr($body, 40, 2));  // 1 byte for number of satellites


        // Logging for debugging
        Log::info("GPS Location: Lat: $latitude, Lon: $longitude, Speed: $speed km/h, Direction: $direction, Altitude: $altitude m, Accuracy: $accuracy, Mileage: $mileage m, Satellites: $satellites");

        // Store GPS location in the database
        GPSLocation::create([
            'device_id' => $device->id,
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
//    Update
    private function handleAlarmCode($device, $value)
    {
        // Validate input length
        if (strlen($value) < 16) {
            Log::error("Invalid alarm data received for device {$device->imei}");
            return;
        }

        // Extract the 4-byte Alarm Code (Bytes 0-3) and convert it from little-endian to decimal
        $alarmCodeHex = mb_substr($value, 0, 8);
        $alarmCode = hexdec(implode('', array_reverse(str_split($alarmCodeHex, 2))));
        Log::info("Alarm code: $alarmCode");
        $alarmBinary = str_pad(decbin($alarmCode), 32, '0', STR_PAD_LEFT);
        Log::info("Alarm Binary: $alarmBinary");

        // Extract the 4-byte UTC Timestamp (Bytes 3-6) and convert from little-endian to decimal
        $timestampHex = mb_substr($value, 8, 8);
        $timestamp = hexdec(implode('', array_reverse(str_split($timestampHex, 2))));
        $triggeredAt = $timestamp > 0 ? gmdate("Y-m-d H:i:s", $timestamp) : null;

        // Check if the timestamp is within the last 5 minutes
        $currentTimestamp = time();
        if ($timestamp <= 0 || ($currentTimestamp - $timestamp) > 300) {
            Log::warning("Device {$device->imei} Alarm ignored due to old timestamp: " . ($triggeredAt ?? "Invalid Timestamp"));
            return;
        }

        // Debugging: Verify extracted values
        Log::info("Device {$device->imei} Alarm UTC Timestamp: " . ($triggeredAt ?? "Invalid Timestamp"));
        Log::info("Device {$device->imei} Alarm Binary: " . $alarmBinary);

        // Define alarm mappings using bitwise flags
        $alarmData = [
            'device_id'               => $device->id,
            'triggered_at'            => $triggeredAt,
            'battery_low_alert'       => $alarmBinary[31] === '1',
            'over_speed_alert'        => $alarmBinary[30] === '1',
            'fall_down_alert'         => $alarmBinary[29] === '1',
            'welfare_alert'           => $alarmBinary[28] === '1',
            'geo_1_alert'             => $alarmBinary[27] === '1',
            'geo_2_alert'             => $alarmBinary[26] === '1',
            'geo_3_alert'             => $alarmBinary[25] === '1',
            'geo_4_alert'             => $alarmBinary[24] === '1',
            'power_off_alert'         => $alarmBinary[23] === '1',
            'power_on_alert'          => $alarmBinary[22] === '1',
            'motion_alert'            => $alarmBinary[21] === '1',
            'no_motion_alert'         => $alarmBinary[20] === '1',
            'sos_alert'               => $alarmBinary[19] === '1',
            'side_call_button_1'      => $alarmBinary[18] === '1',
            'side_call_button_2'      => $alarmBinary[17] === '1',
            'battery_charging_start'  => $alarmBinary[16] === '1',
            'no_charging'             => $alarmBinary[15] === '1',
            'sos_ending'              => $alarmBinary[14] === '1',
            'amber_alert'             => $alarmBinary[13] === '1',
            'welfare_alert_ending'    => $alarmBinary[12] === '1',
            'fall_down_ending'        => $alarmBinary[11] === '1',
            'one_day_upload'          => $alarmBinary[10] === '1',
            'beacon_absence'          => $alarmBinary[9] === '1',
            'bark_detection'          => $alarmBinary[8] === '1',
            'ble_disconnected'        => $alarmBinary[1] === '1',
            'watch_taken_away'        => $alarmBinary[0] === '1',
        ];

        // Store alarm in the database
        DeviceAlarm::create($alarmData);

        // Log confirmation
        Log::info("Device {$device->imei} Alarm recorded at: " . ($triggeredAt ?? "Unknown Time"));
    }


    /**
     * Handle General Status (Key 0x24).
     */
    private function handleGeneralStatus($device, $value)
    {
        // Convert little-endian hex to big-endian for correct parsing
        $timestampHex = substr($value, 0, 8);
        $timestamp = hexdec(implode('', array_reverse(str_split($timestampHex, 2))));

        $statusHex = substr($value, 8, 8);
        $statusBin = str_pad(base_convert(implode('', array_reverse(str_split($statusHex, 2))), 16, 2), 32, '0', STR_PAD_LEFT);

        $status2Hex = substr($value, 16, 8);
        $status2Bin = str_pad(base_convert(implode('', array_reverse(str_split($status2Hex, 2))), 16, 2), 32, '0', STR_PAD_LEFT);

        // Decode status bits from binary representation
        $decodedStatus = [
            'gps' => $statusBin[31] === '1',
            'wifi_source' => $statusBin[30] === '1',
            'cell_tower' => $statusBin[29] === '1',
            'ble_location' => $statusBin[28] === '1',
            'in_charging' => $statusBin[27] === '1',
            'fully_charged' => $statusBin[26] === '1',
            'reboot' => $statusBin[25] === '1',
            'historical_data' => $statusBin[24] === '1',
            'agps_data_valid' => $statusBin[23] === '1',
            'motion' => $statusBin[22] === '1',
            'smart_locating' => $statusBin[21] === '1',
            'beacon_location' => $statusBin[20] === '1',
            'ble_connected' => $statusBin[19] === '1',
            'fall_down_allow' => $statusBin[18] === '1',
            'home_wifi_location' => $statusBin[17] === '1',
            'indoor_outdoor_location' => $statusBin[16] === '1',
            'work_mode' => bindec(substr($statusBin, 13, 3)), // Bits 16-18
            'cell_signal_strength' => bindec(substr($statusBin, 8, 5)), // Bits 19-23
            'battery_level' => bindec(substr($statusBin, 0, 8)), // Bits 24-31
        ];

        // Decode status2 bits
        $networkTypeBits = bindec(substr($status2Bin, 29, 3)); // Bits 0-2
        $networkTypes = ['No service', '2G', '3G', '4G'];
        $mobileNetworkType = $networkTypes[$networkTypeBits] ?? 'Unknown';

        $decodedStatus2 = [
            'mobile_network_type' => $mobileNetworkType,
        ];

        // Log details
        Log::info("Timestamp: " . gmdate("Y-m-d H:i:s", $timestamp));
        Log::info("Status Bits (Binary): " . $statusBin);
        Log::info("Decoded Status:", $decodedStatus);
        Log::info("Status2 Bits (Binary): " . $status2Bin);
        Log::info("Decoded Status2:", $decodedStatus2);
        Log::info("General Status for Device {$device->imei} saved successfully.");

        // Save to database
        GeneralStatus::create(array_merge([
            'device_id' => $device->id,
            'status_time' => gmdate("Y-m-d H:i:s", $timestamp),
        ], $decodedStatus, $decodedStatus2));
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
        $header = "AB"; // Message header
        $properties = "00"; // No encryption, no ACK request
        $length = "0003"; // Length of the message body (3 bytes)
        $lengthSwapped = "0300";
         $crc = "C708"; // Precomputed CRC for 7F0100 (no need to recalculate)
        $crcSwapped = "08C7";

        $command = "7F"; // ACK command
        $keyLength = "01"; // Length of the key field
        $key = "00"; // Negative Response Key

        // Swap sequence ID (little-endian)
        $swappedSequenceId = substr($sequenceId, 2, 2) . substr($sequenceId, 0, 2);



        // Construct the full message as a hex string
        $message = $header . $properties . $lengthSwapped . $crcSwapped . $sequenceId . $command . $keyLength . $key;

        return strtolower($message); // Return as uppercase hex string
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
    private function littleEndianHexDec($hex) {
        // Split hex string into 2-character chunks (bytes)
        $bytes = str_split($hex, 2);
        // Reverse the order of bytes
        $reversedBytes = array_reverse($bytes);
        // Join bytes back into a hex string
        $reversedHex = implode("", $reversedBytes);
        // Convert the reversed hex string to decimal
        return hexdec($reversedHex);
    }
}
