<?php

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = 'user_address';

    protected $fillable = ['user_id', 'address_id', 'type'];
    protected $casts = [
        'type' => AddressType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
