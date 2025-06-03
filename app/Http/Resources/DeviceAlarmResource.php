<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceAlarmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $alerts = [];
        if ($this->fall_down_alert) $alerts[] = 'Valalarm';
        if ($this->sos_alert) $alerts[] = 'Noodoproep';
        // Add more alerts as needed

        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toISOString(),
            'triggered_alerts' => implode(', ', $alerts) ?: 'Geen',
            'false_alarm' => $this->is_false_alarm,
            'location' => $this->device && $this->device->latestLocation
                ? new LocationResource($this->device->latestLocation)
                : null,

            'device' => [
                'imei' => optional($this->device)->imei,
                'phone_number' => optional($this->device)->phone_number,
                'connection_number' => optional($this->device)->connection_number,
                'user' => [
                    'name' => optional(optional($this->device)->user)->name,
                ],
                // 👇 Caregivers en route added here
                'caregivers_en_route' => $this->caregivers_en_route ?? 'Geen',
            ],
        ];
    }
}
