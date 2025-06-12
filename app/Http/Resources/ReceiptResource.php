<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'status' => $this->status,
            'receipt_date' => $this->receipt_date?->toDateString(),
            'receipt_date_formatted' => $this->receipt_date?->format('M d, Y'),
            'total_amount' => $this->calculateTotalAmount(),
            'total_amount_formatted' => number_format($this->calculateTotalAmount(), 0),
            'bets' => BetResource::collection($this->whenLoaded('bets')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at?->format('M d, Y h:i A'),
            'updated_at' =>  $this->updated_at?->format('M d, Y h:i A'),
        ];
    }
}
