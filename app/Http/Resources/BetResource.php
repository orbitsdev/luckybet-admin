<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\DrawResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\GameTypeResource;
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
            'ticket_id' => $this->ticket_id,
            'bet_number' => $this->bet_number,
            'amount' => $this->amount,
            'status' => $this->status,
            'game_type' => new GameTypeResource($this->whenLoaded('gameType')),
            'draw' => new DrawResource($this->whenLoaded('draw')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'customer' => $this->when($this->customer_id, new UserResource($this->whenLoaded('customer'))),
            'is_combination' => $this->is_combination,
            'bet_date' => $this->bet_date,
            'created_at' => $this->created_at,
        ];
    }
}
