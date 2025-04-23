<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\InviteStatus;

class Invite extends Model
{
    protected $fillable = [
        'inviter_id',
        'invited_user_id',
        'email',
        'phone_number',
        'token',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'status' => InviteStatus::class,
        'expires_at' => 'datetime',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitedUser()
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }
}
