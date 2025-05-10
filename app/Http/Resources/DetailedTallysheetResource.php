<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailedTallysheetResource extends JsonResource
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
            'game_type' => $this['game_type'],
            'total_amount' => $this['total_amount'],
            'total_amount_formatted' => $this['total_amount_formatted'],
            'bets' => $this['bets'],
            'bets_by_game_type' => $this['bets_by_game_type'] ?? [
                'S2' => [],
                'S3' => [],
                'D4' => []
            ],
            'pagination' => $this['pagination'] ?? null,
        ];
    }
}
