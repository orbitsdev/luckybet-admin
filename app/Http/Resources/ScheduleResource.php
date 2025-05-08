<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
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
            'draw_time' => $this->draw_time,
            'draw_time_formatted' => \Carbon\Carbon::createFromFormat('H:i:s', $this->draw_time)->format('g:i A'),
        ];
    }
}
