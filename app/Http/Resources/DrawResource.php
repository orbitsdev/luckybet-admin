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
            'draw_date_formatted' => \Carbon\Carbon::createFromFormat('Y-m-d', $this->draw_date)->format('M d, Y'),
            'draw_time' => $this->draw_time,
            'draw_time_formatted' => \Carbon\Carbon::createFromFormat('H:i:s', $this->draw_time)->format('g:i A'),
            'is_open' => $this->is_open,
            'is_active' => $this->is_active,
        ];
    }
}
