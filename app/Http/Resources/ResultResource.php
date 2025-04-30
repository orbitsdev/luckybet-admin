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
            'type' => $this->type,
            'winning_number' => $this->winning_number,
            'coordinator' => new UserResource($this->whenLoaded('coordinator')),
            'draw' => $this->when($this->relationLoaded('draw'), function() {
                return [
                    'id' => $this->draw->id,
                    'draw_date' => $this->draw->draw_date,
                    'draw_time' => $this->draw->draw_time,
                    'type' => $this->draw->type,
                ];
            }),
        ];
    }
}
