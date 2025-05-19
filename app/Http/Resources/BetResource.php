<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\DrawResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\GameTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // ...existing fields...
            'bet_type_draw_label' => $this->getBetTypeDrawLabel(),
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'bet_number' => $this->bet_number,
            'winning_amount' => is_null($this->winning_amount)
    ? null
    : (floor((float)$this->winning_amount) == (float)$this->winning_amount
        ? (int)$this->winning_amount
        : (float)$this->winning_amount),
'winning_amount_formatted' => is_null($this->winning_amount)
    ? null
    : (floor((float)$this->winning_amount) == (float)$this->winning_amount
        ? number_format($this->winning_amount, 0, '.', ',')
        : number_format($this->winning_amount, 2, '.', ',')),
            'is_low_win' => $this->is_low_win,
            'amount' => floor((float)$this->amount) == (float)$this->amount 
                ? number_format((float)$this->amount, 0, '.', '') 
                : $this->amount,
'amount_formatted' => is_null($this->amount)
    ? null
    : (floor((float)$this->amount) == (float)$this->amount
        ? number_format($this->amount, 0, '.', ',')
        : number_format($this->amount, 2, '.', ',')),
            'is_claimed' => $this->is_claimed,
            'is_rejected' => $this->is_rejected,
            'is_combination' => $this->is_combination,
            'is_winner' => $this->is_winner,
            'd4_sub_selection' => $this->when($this->d4_sub_selection, $this->d4_sub_selection),
            'bet_date' => $this->bet_date,
            'bet_date_formatted' => $this->bet_date ? $this->bet_date->format('M d, Y h:i A') : null,
            'claimed_at' => $this->when($this->is_claimed, $this->claimed_at),
            'claimed_at_formatted' => $this->when($this->is_claimed && $this->claimed_at, $this->claimed_at ? $this->claimed_at->format('M d, Y h:i A') : null),
            'created_at' => $this->created_at,
            'game_type' => new GameTypeResource($this->whenLoaded('gameType')),
            'draw' => new DrawResource($this->whenLoaded('draw')),
            'teller' => new UserResource($this->whenLoaded('teller')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'customer' => $this->when($this->customer_id, new UserResource($this->whenLoaded('customer'))),
        ];
    }
    /**
     * Compute the bet type draw label for frontend display.
     */
    private function getBetTypeDrawLabel()
    {
        // Draw time (e.g., 9PM)
        $drawTime = $this->draw && isset($this->draw->draw_time_simple)
            ? $this->draw->draw_time_simple
            : ($this->draw && isset($this->draw->draw_time) ? date('ga', strtotime($this->draw->draw_time)) : '');
        $code = $this->gameType && isset($this->gameType->code) ? strtoupper($this->gameType->code) : '';
        $d4Sub = $this->d4_sub_selection;

        // If S2/S3 with a parent that is D4, use parent's draw time and D4 label
        if (($code === 'S2' || $code === 'S3') && $this->parent_id && $this->parent && $this->parent->gameType && strtoupper($this->parent->gameType->code) === 'D4') {
            $parentDrawTime = $this->parent->draw && isset($this->parent->draw->draw_time_simple)
                ? $this->parent->draw->draw_time_simple
                : ($this->parent->draw && isset($this->parent->draw->draw_time) ? date('ga', strtotime($this->parent->draw->draw_time)) : '');
            return $parentDrawTime . 'D4-' . $code;
        }

        // If D4 with sub-selection
        if (($code === 'D4' || $code === '4D') && $d4Sub) {
            return $drawTime . $code . '-' . strtoupper($d4Sub);
        }

        // Default: just draw time + code
        return $drawTime . $code;
    }
}

