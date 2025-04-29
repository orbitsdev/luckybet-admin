<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommissionSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'commission_percentage_standard' => $this->commission_rate, // from User model
            'total_commission_percentage' => $this->commission_rate,    // same or fixed 10%
            'total_commission_amount' => $this->total_commission_amount, // manually passed/calculated
        ];
    }
}
