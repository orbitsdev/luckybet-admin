<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TallysheetReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this['date'],
            'gross' => $this['gross'],
            'hits' => $this['hits'],
            'kabig' => $this['kabig'],
            'voided' => $this['voided'],
            'per_draw' => $this['per_draw'],
        ];
    }
}
