<?php

function parseHexData($hexData)
{
    $header = substr($hexData, 0, 2); // Should be "ab"
    if ($header !== "ab") {
        echo "Invalid header: $header\n";
        return;
    }

    $properties = substr($hexData, 2, 2);
    $length = hexdec(substr($hexData, 4, 4)); // Length of message body
    $crc = substr($hexData, 8, 4);
    $sequenceId = substr($hexData, 12, 4);
    $body = substr($hexData, 16);

    echo "Header: $header\n";
    echo "Properties: $properties\n";
    echo "Length: $length\n";
    echo "CRC: $crc\n";
    echo "Sequence ID: $sequenceId\n";

    $offset = 0;

    // Parse the body for commands and keys
    while ($offset < strlen($body)) {
        $command = substr($body, $offset, 2); // Command byte
        $offset += 2;

        $keyLength = hexdec(substr($body, $offset, 2)); // Length of key-value pair
        $offset += 2;

        $key = substr($body, $offset, 2); // Key byte
        $offset += 2;

        $valueHex = substr($body, $offset, $keyLength * 2); // Key's value
        $offset += $keyLength * 2;

        echo "Command: $command, Key: $key, Value: $valueHex\n";

        // Handle specific keys
        if ($key === '20') {
            echo "Key 20 (GPS Data) Found!\n";
            parseGPSData($valueHex);
        }
    }
}

function parseGPSData($valueHex)
{
    // Assuming GPS Data (latitude, longitude, etc.)
    $latitude = hexdec(substr($valueHex, 0, 8)) / 10000000.0; // Convert to decimal degrees
    $longitude = hexdec(substr($valueHex, 8, 8)) / 10000000.0;
    $speed = hexdec(substr($valueHex, 16, 4)); // Speed in km/h
    $direction = hexdec(substr($valueHex, 20, 4)); // Direction in degrees
    $altitude = hexdec(substr($valueHex, 24, 4)); // Altitude in meters

    echo "Parsed GPS Data:\n";
    echo "- Latitude: $latitude\n";
    echo "- Longitude: $longitude\n";
    echo "- Speed: $speed km/h\n";
    echo "- Direction: $direction degrees\n";
    echo "- Altitude: $altitude meters\n";
}

$hexData = "ab109f0318e92a000110013836323331313036373532333139390d24639d86670603ce39030000004022c8704f57fa4960bf50e039aeaae1b644fe3be4858ea96802b8eae353a96a0298eae353a748d343de6519a6d1acae72249ea5158525d93072a4bc30d92585170c2bcc0008000f6f8420cb76000d2405a486670103fe38030000001620ea55ae1e290446030000000018000a00000000000b0d24f1a886670103ee37030000001620c859ae1ea0054603000000001d000b0000000000090d2445ab86670601c637030000006a22c7704f57fa4960b5f5699e948382b48283949e69f3b044fe3be4858ead6a0298eae353ad8c85e43bfe6aa9728660d6c85ea96802b8eae353a850c8d6608672a7b8d526dd7c71a7909f220d72a9a6f80da9117d22a55ad343de6519a55c648e58b981a1a0a71bb12c100c2bcc0008000e6f8420cb76000d24baad86670103be370300000016204a57ae1e5b0446030000000017000a00000000000a0d24d1bb86670600e635030000004722cb704f57fa4960bd50e039aeaae1b7f5699e948382b78283949e69f3b044fe3be4858ea9b8d526dd7c71a85ad343de6519a8f80da9117d22a538437d6ae255a43a431d6ae2550c2bcc000800106f8420cb76000d24e2c986670600c633030000005c22ca704f57fa4960be50e039aeaae1b7f5699e948382b58283949e69f3a85ad343de6519a6b8d526dd7c71a6a0a71bb12c10a6f80da9117d22a69c2472aeacd6a56802b8eae353a438437d6ae255a41835d11315e1a46a0298eae3530c2bcc0008000f6f8420cb76000d24f2d786670600ce30030000005c22c1704f57fa4960bf50e039aeaae1b844fe3be4858ea85ad343de6519a650c8d6608672a548d343c8b6f9a55c648e58b981a5728660d6c85ea5b8d526dd7c71a538437d6ae255a43a431d6ae255a3d43df385fdc1a36a0298eae3530c2bcc0008000f6f8420cb76000d2402e686670600ce28030000006a22c6704f57fa4960c05ce50cad7e8fbc50e039aeaae1b844fe3be4858eb58c85e43bfe6ab3f5699e948382b18283949e69f3ad8283949d1f36a76a0298eae353a66802b8eae353a550c8d6608672a55c648e58b981a4728660d6c85ea4909f220d72a9a4a0a71bb12c100c2bcc0008000f6f8420cb76000d2412f486670600e616030000004722c9704f57fa4960b750e039aeaae1b4f5699e948382b444fe3be4858eb48283949e69f3ab48d343de6519ab5ad343de6519a6f80da9117d22a36a0298eae353a3a0a71bb12c100c2bcc000800106f8420cb7600
";
parseHexData($hexData);
