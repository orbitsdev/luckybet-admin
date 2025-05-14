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
            // Smart number formatting - only show decimal places if needed
            'amount' => floor((float)$this->amount) == (float)$this->amount 
                ? number_format((float)$this->amount, 0, '.', '') 
                : $this->amount,
            'is_claimed' => $this->is_claimed,
            'is_rejected' => $this->is_rejected,
            'is_combination' => $this->is_combination,
            'd4_sub_selection' => $this->when($this->d4_sub_selection, $this->d4_sub_selection),
            'bet_date' => $this->bet_date,
            'bet_date_formatted' => $this->bet_date ? $this->bet_date->format('M d, Y h:i A') : null,
            'claimed_at' => $this->when($this->is_claimed, $this->claimed_at),
            'claimed_at_formatted' => $this->when($this->is_claimed && $this->claimed_at, $this->claimed_at ? $this->claimed_at->format('M d, Y h:i A') : null),
            'created_at' => $this->created_at,
            'game_type' => new GameTypeResource($this->whenLoaded('gameType')),
            'draw' => new DrawResource($this->whenLoaded('draw')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'customer' => $this->when($this->customer_id, new UserResource($this->whenLoaded('customer'))),
        ];
    }
}
