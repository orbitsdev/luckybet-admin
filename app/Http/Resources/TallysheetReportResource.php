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
            'date_formatted' => $this['date_formatted'],
            'gross' => $this['gross'],
            'gross_formatted' => $this['gross_formatted'] ?? number_format($this['gross'], 2),
            'sales' => $this['sales'],
            'sales_formatted' => $this['sales_formatted'] ?? number_format($this['sales'], 2),
            'hits' => $this['hits'],
            'hits_formatted' => $this['hits_formatted'] ?? number_format($this['hits'], 2),
            'kabig' => $this['kabig'],
            'kabig_formatted' => $this['kabig_formatted'] ?? number_format($this['kabig'], 2),
            'voided' => $this['voided'],
            'voided_formatted' => $this['voided_formatted'] ?? number_format($this['voided'], 2),
            'per_draw' => $this['per_draw'],
        ];
    }
}
