<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlarmNote extends Model
{
    protected $fillable = [
        'device_alarm_id',
        'user_id',
        'note',
    ];

    public function deviceAlarm(): BelongsTo
    {
        return $this->belongsTo(DeviceAlarm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
