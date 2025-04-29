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
            'name' => $this->teller_name,       // Teller name
            'sales' => $this->sales,             // Total sales
            'hits' => $this->hits,               // Total hits
            'gross' => $this->gross,             // Gross = sales - hits
        ];
    }
}
