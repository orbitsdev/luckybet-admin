<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\DrawTallySheetResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TellerTallySheetSummaryResource extends JsonResource
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
                'gross' => $this->gross,
                'sales' => $this->sales,
                'hits' => $this->hits,
                'voided' => $this->voided,
            ],
            'draws' => DrawTallySheetResource::collection($this->draws),
        ];
    }
}
