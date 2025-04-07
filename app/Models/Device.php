<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    // A device can have many alarms record
    public function alarms()
    {
        return $this->hasMany(DeviceAlarm::class);
    }

    public function gpsLocations()
    {
        return $this->hasMany(GPSLocation::class);
    }

    public function generalStatuses()
    {
        return $this->hasMany(GeneralStatus::class);
    }

    /**
     * Get the latest non-expired emergency link.
     */
    public function latestEmergencyLink()
    {
        return $this->alarms()
            ->whereHas('emergencyLink', function ($query) {
                $query->where('expires_at', '>', now()); // Filter non-expired links
            })
            ->with('emergencyLink') // Eager load the emergency link
            ->orderBy('triggered_at', 'desc') // Order by most recent alarm
            ->first()?->emergencyLink; // Return the associated EmergencyLink
    }

    public function latestLocation()
    {
        return $this->hasOne(GPSLocation::class)->latestOfMany();
    }

    public function latestStatus()
    {
        return $this->hasOne(GeneralStatus::class)->latestOfMany();
    }

    public function latestAlarm()
    {
        return $this->hasOne(DeviceAlarm::class)->latestOfMany();
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if ($user->patients()->exists()) {
            return $query->whereIn('user_id', $user->patients->pluck('id'));
        }

        return $query->where('user_id', $user->id);
    }
}
