<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\DeviceAlarm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceAlarmSeeder extends Seeder
{
    public function run(): void
    {
        $devices = Device::all();

        foreach ($devices as $device) {
            DeviceAlarm::create([
                'device_id' => $device->id,
                'triggered_at' => now()->subMinutes(rand(1, 1000)),
                'is_false_alarm' => fake()->boolean(),

                // Randomized alert fields
                'battery_low_alert' => fake()->boolean(),
                'over_speed_alert' => fake()->boolean(),
                'fall_down_alert' => fake()->boolean(),
                'welfare_alert' => fake()->boolean(),
                'geo_1_alert' => fake()->boolean(),
                'geo_2_alert' => fake()->boolean(),
                'geo_3_alert' => fake()->boolean(),
                'geo_4_alert' => fake()->boolean(),
                'power_off_alert' => fake()->boolean(),
                'power_on_alert' => fake()->boolean(),
                'motion_alert' => fake()->boolean(),
                'no_motion_alert' => fake()->boolean(),
                'sos_alert' => fake()->boolean(),
                'side_call_button_1' => fake()->boolean(),
                'side_call_button_2' => fake()->boolean(),
                'battery_charging_start' => fake()->boolean(),
                'no_charging' => fake()->boolean(),
                'sos_ending' => fake()->boolean(),
                'amber_alert' => fake()->boolean(),
                'welfare_alert_ending' => fake()->boolean(),
                'fall_down_ending' => fake()->boolean(),
                'one_day_upload' => fake()->boolean(),
                'beacon_absence' => fake()->boolean(),
                'bark_detection' => fake()->boolean(),
                'ble_disconnected' => fake()->boolean(),
                'watch_taken_away' => fake()->boolean(),
            ]);
        }
    }
}
