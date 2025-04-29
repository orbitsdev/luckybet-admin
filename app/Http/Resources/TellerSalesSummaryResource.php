<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TellerSalesSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'draw_time' => $this->draw_time,          // "14:00:00"
            'gross' => $this->net_gross,              // sales - claims
            'sales' => $this->total_sales,
            'bet' => $this->total_bets,
            'hits' => $this->total_hits,
        ];
    }
}
