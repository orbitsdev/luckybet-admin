<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrawResource extends JsonResource
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
            'type' => $this->type,      // S2, S3, D4
            'is_open' => $this->is_open, // true = betting open, false = betting closed
        ];
    }
}
