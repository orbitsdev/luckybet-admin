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
        try {
            $drawDateFormatted = $this->draw_date ? \Carbon\Carbon::parse($this->draw_date)->format('M d, Y') : null;
            $drawTimeFormatted = $this->draw_time ? \Carbon\Carbon::parse($this->draw_time)->format('g:i A') : null;
        } catch (\Exception $e) {
            $drawDateFormatted = null;
            $drawTimeFormatted = null;
        }
        
        return [
            'id' => $this->id,
            'draw_date' => $this->draw_date,
            'draw_date_formatted' => $drawDateFormatted,
            'draw_time' => $this->draw_time,
            'draw_time_formatted' => $drawTimeFormatted,
            'is_open' => $this->is_open,
            'is_active' => $this->is_active,
        ];
    }
}
