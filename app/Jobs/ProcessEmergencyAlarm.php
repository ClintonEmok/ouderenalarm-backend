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

            // Encode SIA message
            $encoder = new SiaEncoderService();
            $eventCode = 'QA'; // Emergency alarm
            $accountId = '1234';
            $extraInfo = $emergencyLink->link; // Emergency link

            $encryptedMessage = $encoder->encodeMessage($eventCode, $accountId, $extraInfo);

            // Dispatch SendSiaMessage job
            SendSiaMessage::dispatch($encryptedMessage);

            Log::info("Queued SIA emergency alarm for Alarm {$this->alarm->id} with URL: {$extraInfo}");
      
    }
}
