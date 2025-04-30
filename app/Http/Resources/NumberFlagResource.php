<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NumberFlagResource extends JsonResource
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
            'number' => $this->number,
            'schedule_id' => $this->schedule_id,
            'date' => $this->date,
            'location_id' => $this->location_id,
            'type' => $this->type,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'schedule' => $this->whenLoaded('schedule', function() {
                return [
                    'id' => $this->schedule->id,
                    'name' => $this->schedule->name,
                    'draw_time' => $this->schedule->draw_time,
                ];
            }),
            'location' => $this->whenLoaded('location', function() {
                return [
                    'id' => $this->location->id,
                    'name' => $this->location->name,
                ];
            }),
        ];
    }
}
