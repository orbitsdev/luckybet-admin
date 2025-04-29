<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoordinatorSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'totals' => [
                'sales' => $this->total_sales,
                'hits' => $this->total_hits,
                'gross' => $this->total_gross,
            ],
            'tellers' => TellerSummaryResource::collection($this->tellers),
        ];
    }
}
