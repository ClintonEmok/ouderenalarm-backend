<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaregiverInvitation extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'token', 'patient_id'];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
