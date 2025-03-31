<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DeviceAlarm;

class BackfillCaregiverStatuses extends Command
{
    protected $signature = 'alarms:backfill-caregivers';
    protected $description = 'Attach caregivers to existing device alarms based on the patient of each device';

    public function handle()
    {
        $this->info('Starting backfill...');

        $count = 0;

        DeviceAlarm::with(['device.user.caregivers'])->each(function ($alarm) use (&$count) {
            $patient = $alarm->device->user ?? null;

            if (!$patient) return;

            $caregivers = $patient->caregivers;

            if ($caregivers->isEmpty()) return;

            $alarm->caregiverStatuses()->syncWithoutDetaching(
                $caregivers->pluck('id')->toArray()
            );

            $count++;
        });

        $this->info("Finished. Updated caregiver statuses for {$count} device alarms.");

        return Command::SUCCESS;
    }
}
