<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'address_id',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'user_address')->withTimestamps();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function shippingAddresses()
    {
        return $this->belongsToMany(Address::class, 'user_address')
            ->wherePivot('type','=' ,'shipping')
            ->withTimestamps();
    }

    public function billingAddresses()
    {
        return $this->belongsToMany(Address::class, 'user_address')
            ->wherePivot('type','=', 'billing')
            ->withTimestamps();
    }

    /**
     * Relationship: Users who are caregivers to this user (patients).
     */
    public function caregivers()
    {
        return $this->belongsToMany(
            User::class,
            'caregiver_patients', // Intermediate table
            'patient_id',        // Foreign key on caregiver_patient for this user as a patient
            'caregiver_id'       // Foreign key on caregiver_patient for the caregiver
        )->withTimestamps();
    }

    /**
     * Relationship: Users who are patients of this user (caregiver).
     */
    public function patients()
    {
        return $this->belongsToMany(
            User::class,
            'caregiver_patients', // Intermediate table
            'caregiver_id',      // Foreign key on caregiver_patient for this user as a caregiver
            'patient_id'         // Foreign key on caregiver_patient for the patient
        )->withTimestamps();
    }

    /**
     * Relationship: Caregiver invitations sent to this user.
     */
    public function caregiverInvitations()
    {
        return $this->hasMany(CaregiverInvitation::class, 'patient_id');
    }
}
