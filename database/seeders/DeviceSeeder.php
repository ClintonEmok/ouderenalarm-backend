<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Device;
use App\Models\GPSLocation;
use App\Models\GeneralStatus;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $userCount = 5;
        $devicesPerUser = 2;

        for ($i = 1; $i <= $userCount; $i++) {
            $email = "user$i@example.com";

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "User $i",
                    'password' => bcrypt('password'),
                ]
            );
            if (!$user->hasRole('customer')) {
                $user->assignRole('customer');
            }

            for ($j = 1; $j <= $devicesPerUser; $j++) {
                $deviceIndex = ($i - 1) * $devicesPerUser + $j;

                $device = Device::create([
                    'user_id' => $user->id,
                    'imei' => (string) rand(100000000000000, 999999999999999),
                    'nickname' => "Device $deviceIndex",
                    'ip_address' => "192.168.1." . rand(2, 254),
                    'port' => rand(1024, 65535),
                    'phone_number' => '+3161234' . str_pad($deviceIndex, 4, '0', STR_PAD_LEFT),
                    'status' => 'active',
                ]);

                $device->gpsLocations()->create([
                    'latitude' => 52.3676 + mt_rand(-100, 100) / 1000,
                    'longitude' => 4.9041 + mt_rand(-100, 100) / 1000,
                    'speed' => rand(0, 80),
                    'direction' => rand(0, 359),
                    'altitude' => rand(0, 300),
                    'horizontal_accuracy' => rand(1, 10),
                    'mileage' => rand(0, 10000),
                    'satellites' => rand(5, 10),
                ]);

                $device->generalStatuses()->create([
                    'status_time' => now(),
                    'battery_level' => rand(10, 100),

                    'gps' => rand(0, 1),
                    'wifi_source' => rand(0, 1),
                    'cell_tower' => rand(0, 1),
                    'ble_location' => rand(0, 1),
                    'in_charging' => rand(0, 1),
                    'fully_charged' => rand(0, 1),
                    'reboot' => rand(0, 1),
                    'historical_data' => rand(0, 1),
                    'agps_data_valid' => rand(0, 1),
                    'motion' => rand(0, 1),
                    'smart_locating' => rand(0, 1),
                    'beacon_location' => rand(0, 1),
                    'ble_connected' => rand(0, 1),
                    'fall_down_allow' => rand(0, 1),
                    'home_wifi_location' => rand(0, 1),
                    'indoor_outdoor_location' => rand(0, 1),

                    'mobile_network_type' => collect(['No service', '2G', '3G', '4G'])->random(),
                    'work_mode' => rand(1, 6),
                    'cell_signal_strength' => rand(0, 31),
                    'battery_description' => rand(0, 100),
                ]);
            }
        }
    }
}
