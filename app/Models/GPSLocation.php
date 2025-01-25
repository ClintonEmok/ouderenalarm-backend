<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GPSLocation extends Model
{
    //
protected $guarded = [];
    protected $table = "gps_locations";
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
