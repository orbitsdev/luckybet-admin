<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BetResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ResultResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimResource extends JsonResource
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
            'amount' => $this->amount,
            'commission_amount' => $this->commission_amount,
            'claimed_at' => $this->claimed_at,
            'qr_code_data' => $this->qr_code_data,
            'bet' => new BetResource($this->whenLoaded('bet')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'result' => new ResultResource($this->whenLoaded('result')),
        ];
    }
}
