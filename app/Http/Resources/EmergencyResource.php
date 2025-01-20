<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $device = $this->deviceAlarm->device;
        $user = $device->user;

        return [
            'device' => [
                'id' => $device->id,
                'imei' => $device->imei,
                'phone_number' => $device->phone_number,
            ],
            'triggered_at' => $this->deviceAlarm->triggered_at,
            'alerts' => [
                'fall_down_alert' => $this->deviceAlarm->fall_down_alert,
                'sos_alert' => $this->deviceAlarm->sos_alert,
            ],
            'user' => [
                'name' => $user->name,
                'age' => now()->diffInYears($user->birthdate),
                'address' => $user->address,
            ],
            'caregivers' => $user->caregivers->map(function ($caregiver) {
                return [
                    'name' => $caregiver->name,
                    'phone' => $caregiver->phone_number,
                    'email' => $caregiver->email,
                ];
            }),
        ];
    }
}
