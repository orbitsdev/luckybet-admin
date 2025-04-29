<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'location' => new LocationResource($this->whenLoaded('location')),
            'is_active' => $this->is_active,
            'profile_photo_url' => $this->profile_photo_url,
        ];
    }
}
