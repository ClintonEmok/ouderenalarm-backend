<?php

namespace App\Jobs;

use App\Filament\Resources\DeviceAlarmResource;
use App\Jobs\SendSiaMessage;
use App\Models\DeviceAlarm;
use App\Services\SiaEncoderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmergencyAlarm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $alarm;

    /**
     * Create a new job instance.
     */
    public function __construct(DeviceAlarm $alarm)
    {
        $this->alarm = $alarm;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // 1. Determine event code based on alarm flags
        $eventCode = match (true) {
            $this->alarm->fall_down_alert => 'NMA',
            $this->alarm->sos_alert => 'NQA',
            default => null,
        };

        if (!$eventCode) {
            Log::warning("Unknown alarm type for Alarm {$this->alarm->id}");
            return;
        }

        // 2. Get connection number safely
        $connectionNumber = $this->alarm->device?->connection_number;
        $account = $this->extractNumbers($connectionNumber);

        if (!$connectionNumber) {
            Log::warning("Missing device or connection number for Alarm {$this->alarm->id}");
            return;
        }

        // 3. Generate extra info URL
        $extraInfo = DeviceAlarmResource::getUrl('view-latest', ['record' => $connectionNumber]);
        Log::info("Generated URL: {$extraInfo}");

        // 5. Dispatch SIA message
        SendSiaMessageJob::dispatch(
            config('app.meldkamer_server'),
            config('app.meldkamer_port'),
            $account,
            $eventCode,
            $extraInfo
        );

        Log::info("Queued SIA {$eventCode} alarm for Alarm {$this->alarm->id} with URL: {$extraInfo}");

        // 6. Send mobile push notification
        $user = $this->alarm->device?->user;

        if (!$user) {
            Log::warning("No user linked to device for Alarm {$this->alarm->id}");
            return;
        }

        $title = 'Alarm Geactiveerd';

        $userName = $this->alarm->device?->user?->name ?? 'Een gebruiker';
        $deviceName = $this->alarm->device?->nickname ? " (apparaat: {$this->alarm->device->nickname})" : '';

        $body = match (true) {
            $this->alarm->fall_down_alert => "{$userName} is mogelijk gevallen{$deviceName}. Controleer alstublieft direct.",
            $this->alarm->sos_alert => "{$userName} heeft op de SOS-knop gedrukt{$deviceName}. Reageer alstublieft zo snel mogelijk.",
            default => "Er is een noodalarm geactiveerd door {$userName}{$deviceName}.",
        };

        $data = [
            'alarm_id' => $this->alarm->id,
            'device_id' => $this->alarm->device_id,
            'type' => $eventCode,
            'url' => $extraInfo,
        ];

        app(\App\Services\NotificationService::class)->sendToUserAndCaregivers(
            $user,
            $title,
            $body,
            $data
        );

        Log::info("Dispatched push notification for Alarm {$this->alarm->id}");
    }

    /**
     * Extract only digits from a string
     */
    protected function extractNumbers(string $input): string
    {
        return preg_replace('/\D+/', '', $input);
    }
}
