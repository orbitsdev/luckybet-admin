<?php

namespace App\Http\Resources;

use App\Models\GameType;
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
        // ✅ Dynamic game types
        $gameTypes = GameType::pluck('code')->toArray();

        return [
            'date' => $this['date'],
            'date_formatted' => $this['date_formatted'],
            'game_type' => $this['game_type'],
            'total_amount' => $this['total_amount'],
            'total_amount_formatted' => $this['total_amount_formatted'],
            'bets' => $this['bets'],
            'bets_by_game_type' => $this['bets_by_game_type'] ?? array_fill_keys($gameTypes, []),
            'pagination' => $this['pagination'] ?? null,
        ];
    }
}
