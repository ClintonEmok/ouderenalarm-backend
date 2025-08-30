<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\GeneralStatus;
use Carbon\Carbon;

class DeviceStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        // Pak alle devices
        $devices = Device::all();

        foreach ($devices as $device) {
            // Voor de laatste 7 dagen (inclusief vandaag)
            for ($d = 0; $d < 7; $d++) {
                $day = Carbon::now()->subDays($d);

                // Voor elk uur van die dag
                for ($h = 0; $h < 24; $h++) {
                    $timestamp = $day->copy()->setHour($h)->setMinute(0)->setSecond(0);

                    GeneralStatus::create([
                        'device_id' => $device->id,
                        'status_time' => $timestamp,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
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
}