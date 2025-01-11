<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = ['user_id', 'imei', 'nickname', 'ip_address', 'port', 'phone_number', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
