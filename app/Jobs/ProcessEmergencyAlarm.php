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

        $title = 'Alarm Triggered';
        $body = match (true) {
            $this->alarm->fall_down_alert => 'A fall was detected. Please check immediately.',
            $this->alarm->sos_alert => 'SOS button was pressed. Please respond.',
            default => 'An emergency alarm was triggered.',
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
