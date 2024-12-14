<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'alarm_code' => $this->alarm_code,
            'location' => [
                'longitude' => $this->longitude,
                'latitude' => $this->latitude
            ],
            'maps_link' => $this->maps_link,
            'phone_number' => $this->phone_number,
            'battery_percentage' => $this->battery_percentage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')) // Use a UserResource if needed
        ];
    }
}
