<?php

namespace App\Models;

use App\Jobs\ProcessEmergencyAlarm;
use App\Models\Scopes\OnlyCriticalAlarms;
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
        'is_false_alarm' => 'boolean',
    ];


    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function notes()
    {
        return $this->hasMany(AlarmNote::class);
    }

    /**
     * Define a relationship with EmergencyLink.
     */
    public function emergencyLink()
    {
        return $this->hasOne(EmergencyLink::class);
    }

    public function caregiverStatuses()
    {
        return $this->belongsToMany(Customer::class, 'caregiver_device_alarm', 'device_alarm_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function getCaregiversEnRouteListAttribute()
    {
        return $this->caregiverStatuses
            ->where('pivot.status', 'en_route')
            ->pluck('name')
            ->implode(', ');
    }
    /**
     * Boot the model to handle events.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OnlyCriticalAlarms());
    }
    protected static function boot()
    {
        parent::boot();

        static::created(function ($alarm) {
            if ($alarm->fall_down_alert || $alarm->sos_alert) {
                ProcessEmergencyAlarm::dispatch($alarm);
            }

            $patient = $alarm->device->user;
            if($patient){
                $caregivers = $patient->caregivers;

                // Attach each caregiver to the alarm with default status
                $alarm->caregiverStatuses()->syncWithoutDetaching($caregivers->pluck('id')->toArray());

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

    public function refreshCaregiverStatuses(): void
    {
        if (!$this->device || !$this->device->user) {
            return;
        }

        $patient = $this->device->user;

        // Load caregivers with priority and order them
        $orderedCaregivers = $patient->caregivers()
            ->withPivot('priority')
            ->orderBy('caregiver_patients.priority')
            ->get();

        $orderedCaregiverIds = $orderedCaregivers->pluck('id')->toArray();

        // Attach any new caregivers (preserving priority order in your logic)
        $this->caregiverStatuses()->syncWithoutDetaching(
            collect($orderedCaregiverIds)->mapWithKeys(fn ($id) => [$id => []])->toArray()
        );

        // Remove any caregivers who no longer apply
        $this->caregiverStatuses()->detach(
            $this->caregiverStatuses->pluck('id')->diff($orderedCaregiverIds)
        );
    }

    public function getTriggeredAlertsAttribute(): string
    {
        $alerts = [];

        if ($this->fall_down_alert) {
            $alerts[] = 'Val Alarm';
        }

        if ($this->sos_alert) {
            $alerts[] = 'SOS oproep';
        }


        return implode(', ', $alerts);
    }
}
