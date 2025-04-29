<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TellerSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'teller_id' => $this->teller_id,
            'name' => $this->teller_name,
            'location_name' => $this->location_name,
            'sales' => $this->sales,
            'hits' => $this->hits,
            'gross' => $this->gross,
            'commission_earned' => $this->commission,
            'voided' => $this->voided,
            'total_bets' => $this->total_bets,
            'last_transaction_time' => $this->last_transaction_time,         // Gross = sales - hits
        ];
    }
}
