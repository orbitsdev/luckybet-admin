<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource
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
            'draw_date' => $this->draw_date,
            'draw_time' => $this->draw_time,
            'type' => $this->type,               // S2, S3, D4
            'winning_number' => $this->winning_number,
            'coordinator' => new UserResource($this->whenLoaded('coordinator')),
        ];
    }
}
