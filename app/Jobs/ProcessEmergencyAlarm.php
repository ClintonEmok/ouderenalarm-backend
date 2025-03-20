<?php

namespace App\Jobs;

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
        $emergencyLink = $this->alarm->createEmergencyLink();

        // Determine event code based on alarm flags
        if ($this->alarm->fall_down_alert) {
            $eventCode = 'NMA';
        } elseif ($this->alarm->sos_alert) {
            $eventCode = 'NQA';
        } else {
            // Optional: Set a default or log unexpected case
            Log::warning("Unknown alarm type for Alarm {$this->alarm->id}");
            return;
        }

        // Send SIA message
        $server = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');
        $account = "3203";
        $extraInfo = $emergencyLink->link;

        SendSiaMessageJob::dispatch($server, $port, $account, $eventCode, $extraInfo);

        Log::info("Queued SIA {$eventCode} alarm for Alarm {$this->alarm->id} with URL: {$extraInfo}");
    }
}
