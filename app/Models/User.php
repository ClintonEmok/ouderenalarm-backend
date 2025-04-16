<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

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

    public function userAddresses()
    {
        return $this->hasMany(UserAddress::class);
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

    public function homeAddress()
    {
        return $this->hasOneThrough(
            Address::class,
            UserAddress::class,
            'user_id',
            'id',
            'id',
            'address_id'
        )->where('user_address.type', '=', 'shipping')
            ->orderBy('user_address.created_at');
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

    public function emergenciesRespondingTo()
    {
        return $this->belongsToMany(EmergencyLink::class, 'caregiver_emergency', 'user_id', 'emergency_link_id')->withTimestamps();
    }

    public function assignedAlarms()
    {
        return $this->belongsToMany(DeviceAlarm::class, 'caregiver_device_alarm')
            ->withPivot('status')
            ->withTimestamps();
    }

}
