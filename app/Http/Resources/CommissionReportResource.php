<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommissionReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date' => $this->date,
            'date_formatted' => $this->date_formatted,
            'commission_rate' => $this->commission_rate,
            'commission_rate_formatted' => $this->commission_rate . '%',
            'total_sales' => $this->total_sales,
            'total_sales_formatted' => number_format($this->total_sales, 0, '.', ','),
            'commission_amount' => $this->commission_amount,
            'commission_amount_formatted' => '₱' . number_format($this->commission_amount, 2, '.', ','),
        ];
    }
}
