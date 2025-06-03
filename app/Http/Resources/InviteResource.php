<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InviteResource extends JsonResource
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
            'email' => $this->email,
            'status' => $this->status,
            'token' => $this->token,
            'expires_at' => optional($this->expires_at)->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'user' => $this->whenLoaded('invitedUser', function () {
                return new UserResource($this->invitedUser);
            }),
        ];
    }
}
