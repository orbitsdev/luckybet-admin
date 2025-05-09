<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TodaySalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'sales' => $this['sales'],
            'sales_formatted' => $this['sales_formatted'],
            'commission_rate' => $this['commission_rate'],
            'commission_rate_formatted' => $this['commission_rate_formatted'],
            'cancellations' => $this['cancellations'],
            'cancellations_formatted' => $this['cancellations_formatted'],
        ];
    }
}
