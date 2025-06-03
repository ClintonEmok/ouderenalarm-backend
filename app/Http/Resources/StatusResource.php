<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'battery_level' => $this->battery_level,
            'signal_strength' => $this->signal_strength,
            'status' => $this->status,
            'timestamp' => $this->created_at->toISOString(),
        ];
    }
}
