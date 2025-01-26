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
            'imei' => $this->imei,
            'nickname' => $this->nickname,
            'ip_address' => $this->ip_address,
            'port' => $this->port,
            'phone_number' => $this->phone_number,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'location' => [
                'latitude' => $this->latestLocation->latitude,
                'longitude' => $this->latestLocation->longitude,
            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'user' => new UserResource($this->whenLoaded('user')) // Use a UserResource if needed
        ];
    }
}
