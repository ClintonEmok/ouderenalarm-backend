<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyLink extends Model
{
    use HasFactory;

    protected $fillable = ['device_alarm_id', 'link', 'expires_at'];

    /**
     * Relationship: Link belongs to a DeviceAlarm.
     */
    public function deviceAlarm()
    {
        return $this->belongsTo(DeviceAlarm::class);
    }

    /**
     * Check if the link is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }
}
