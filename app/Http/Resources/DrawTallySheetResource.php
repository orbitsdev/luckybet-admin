<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DrawTallySheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'draw' => $this->draw_label,   // Example: "2S2: 35"
            'gross' => $this->gross,       // sales - claims
            'sales' => $this->sales,
            'hits' => $this->hits,
            'voided' => $this->voided,
        ];
    }
}
