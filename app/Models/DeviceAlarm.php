<?php

namespace App\Models;

use App\Jobs\ProcessEmergencyAlarm;
use App\Services\SiaEncoderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceAlarm extends Model
{
    protected $guarded = [];

    protected $casts = [
        'battery_low_alert' => 'boolean',
        'over_speed_alert' => 'boolean',
        'fall_down_alert' => 'boolean',
        'welfare_alert' => 'boolean',
        'geo_1_alert' => 'boolean',
        'geo_2_alert' => 'boolean',
        'geo_3_alert' => 'boolean',
        'geo_4_alert' => 'boolean',
        'power_off_alert' => 'boolean',
        'power_on_alert' => 'boolean',
        'motion_alert' => 'boolean',
        'no_motion_alert' => 'boolean',
        'sos_alert' => 'boolean',
        'side_call_button_1' => 'boolean',
        'side_call_button_2' => 'boolean',
        'battery_charging_start' => 'boolean',
        'no_charging' => 'boolean',
        'sos_ending' => 'boolean',
        'amber_alert' => 'boolean',
        'welfare_alert_ending' => 'boolean',
        'fall_down_ending' => 'boolean',
        'one_day_upload' => 'boolean',
        'beacon_absence' => 'boolean',
        'bark_detection' => 'boolean',
        'ble_disconnected' => 'boolean',
        'watch_taken_away' => 'boolean',
    ];


    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Define a relationship with EmergencyLink.
     */
    public function emergencyLink()
    {
        return $this->hasOne(EmergencyLink::class);
    }

    /**
     * Boot the model to handle events.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($alarm) {
            if ($alarm->fall_down_alert || $alarm->sos_alert) {
                ProcessEmergencyAlarm::dispatch($alarm);
            }
        });
    }

    protected static function sendToMonitoringServer(string $message)
    {
        $host = config('app.meldkamer_server');
        $port = config('app.meldkamer_port');

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket || !socket_connect($socket, $host, $port)) {
            Log::error('SIA message send failed: ' . socket_strerror(socket_last_error()));
            return;
        }

        socket_write($socket, $message, strlen($message));
//        $response = socket_read($socket, 2048);
        socket_close($socket);

//        Log::info("Monitoring server response: {$response}");
    }

    /**
     * Create an EmergencyLink for this alarm.
     */
    public function createEmergencyLink()
    {
        $nextJsUrl = config('app.frontend_url', 'https://default-nextjs-url.com');
        $uniqueCode = Str::uuid(); // Generate a unique identifier
        $link = "{$nextJsUrl}/emergency/{$uniqueCode}";

        // Create and store the emergency link
        $emergencyLink = $this->emergencyLink()->create([
            'link' => $link,
            'expires_at' => now()->addHours(24), // Set expiration to 24 hours
        ]);

        Log::info("Emergency link generated for alarm {$this->id}: {$link}");

        return $emergencyLink; // Return the created emergency link
    }
}
