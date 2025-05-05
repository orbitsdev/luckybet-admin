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
            'schedule' => $this->whenLoaded('schedule', function() {
                return [
                    'id' => $this->schedule->id,
                    'name' => $this->schedule->name,
                    'draw_time' => $this->schedule->draw_time,
                ];
            }),
            'game_type' => $this->whenLoaded('gameType', function() {
                return [
                    'id' => $this->gameType->id,
                    'code' => $this->gameType->code,
                    'name' => $this->gameType->name,
                ];
            }),
            'is_open' => $this->is_open, // true = betting open, false = betting closed
        ];
    }
}
