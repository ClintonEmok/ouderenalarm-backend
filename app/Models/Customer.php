<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Customer extends User
{
    protected $table = 'users';
    use HasFactory, Notifiable, HasApiTokens, HasRoles;
    protected $guard_name = 'web';

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'user_address', 'user_id', 'address_id');
    }

    public function getMorphClass()
    {
        // Zorg dat Spatie de rollen koppelt aan model_type = App\Models\User
        return User::class;
    }

}
