<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Address extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_address')->withTimestamps();
    }
}
