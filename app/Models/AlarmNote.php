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
        'is_false_alarm', // ✅ New field
    ];
    protected $casts = [
        'is_false_alarm' => 'boolean',
    ];

    public function deviceAlarm(): BelongsTo
    {
        return $this->belongsTo(DeviceAlarm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (AlarmNote $note) {
            // ✅ Automatically sync the DeviceAlarm based on latest note
            $latest = $note->deviceAlarm->notes()->latest()->first();
            if ($latest) {
                $note->deviceAlarm->update([
                    'is_false_alarm' => $latest->is_false_alarm,
                ]);
            }
        });
    }
}