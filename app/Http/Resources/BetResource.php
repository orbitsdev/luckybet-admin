<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\DrawResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LocationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BetResource extends JsonResource
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
            'bet_number' => $this->bet_number,
            'amount' => $this->amount,
            'game_type' => $this->game_type,           // S2, S3, D4
            'status' => $this->status,                // active, won, lost, claimed, cancelled
            'is_combination' => $this->is_combination, // true/false
            'ticket_id' => $this->ticket_id,           // unique ticket identifier
            'bet_date' => $this->bet_date,
            'draw' => new DrawResource($this->whenLoaded('draw')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'location' => new LocationResource($this->whenLoaded('location')),
        ];
    }
}
