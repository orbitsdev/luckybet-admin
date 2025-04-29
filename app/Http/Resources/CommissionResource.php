<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BetResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ClaimResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionResource extends JsonResource
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
            'rate' => $this->rate,                  // commission rate (e.g., 5.00%)
            'amount' => $this->amount,              // total peso earned
            'commission_date' => $this->commission_date,
            'type' => $this->type,                  // sales or claims
            'bet' => new BetResource($this->whenLoaded('bet')),    // only if from a sale
            'claim' => new ClaimResource($this->whenLoaded('claim')), // only if from a payout
            'teller' => new UserResource($this->whenLoaded('teller')),
        ];
    }
}
