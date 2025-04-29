<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TallySheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'draw_date' => $this->draw_date,
            'teller_name' => $this->teller_name,
            'location_name' => $this->location_name,
            'draw_time' => $this->draw_time,
            'type' => $this->type, // S2, S3, D4
            'total_bets' => $this->total_bets,
            'total_sales' => $this->total_sales,
            'total_hits' => $this->total_hits,
            'total_claims' => $this->total_claims,
            'net_gross' => $this->net_gross, // (Sales - Claims)
            'commission_earned' => $this->commission_earned,
        ];
    }
}
