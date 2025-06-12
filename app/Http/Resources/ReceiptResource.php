<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'receipt_date_formatted' => $this->receipt_date ? Carbon::parse($this->receipt_date)->setTimezone('Asia/Manila')->format('M d, Y') : null,
            'total_amount' => $this->calculateTotalAmount(),
            'total_amount_formatted' => number_format($this->calculateTotalAmount(), 0),
            'bets' => BetResource::collection($this->whenLoaded('bets')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? Carbon::parse($this->created_at)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null,
            'updated_at' => $this->updated_at ? Carbon::parse($this->updated_at)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null,
        ];
    }
}
