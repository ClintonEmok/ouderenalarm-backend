<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEmergencyAlarm;
use App\Models\Device;
use App\Models\DeviceAlarm;
use Illuminate\Console\Command;

class TriggerEmergencyAlarm extends Command
{
    protected $signature = 'alarm:test {--fall} {--sos}';
    protected $description = 'Create a test Device and DeviceAlarm, then trigger the ProcessEmergencyAlarm job';

    public function handle()
    {
        $fall = $this->option('fall');
        $sos = $this->option('sos');

        if (! $fall && ! $sos) {
            $this->error("You must specify at least --fall or --sos.");
            return;
        }

        // Get or create a test device
        $device = Device::firstOrCreate(
            ['imei' => 'test-device-001'], // replace with your actual unique field
            ['phone_number' => '+31600000000',]   // set required fields for Device here
        );

        $this->info("Using device ID {$device->id}");

        // Create a test alarm
        $alarm = DeviceAlarm::create([
            'device_id' => $device->id,
            'fall_down_alert' => $fall,
            'sos_alert' => $sos,
            'triggered_at' => now()
        ]);

        $this->info("Created test DeviceAlarm with ID {$alarm->id}");

        // Dispatch the job
        ProcessEmergencyAlarm::dispatch($alarm);

        $this->info("Dispatched ProcessEmergencyAlarm job.");
    }
}
