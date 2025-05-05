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
            's2_winning_number' => $this->s2_winning_number,
            's3_winning_number' => $this->s3_winning_number,
            'd4_winning_number' => $this->d4_winning_number,
            'coordinator' => new UserResource($this->whenLoaded('coordinator')),
            'draw' => $this->when($this->relationLoaded('draw'), function() {
                return [
                    'id' => $this->draw->id,
                    'draw_date' => $this->draw->draw_date,
                    'draw_time' => $this->draw->draw_time,
                ];
            }),
        ];
    }
}
