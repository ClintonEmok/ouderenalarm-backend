<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaregiverPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'caregiver_id',
        'patient_id',
        'priority'
    ];

    // Define relationships
    public function caregiver()
    {
        return $this->belongsTo(User::class, 'caregiver_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
