<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Draw;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\BetResource;
use App\Http\Resources\DrawResource;

class BettingController extends Controller
{
    public function availableDraws()
    {
        $draws = Draw::where('draw_date', today())
            ->where('is_open', true)
            ->get();

        return ApiResponse::success(DrawResource::collection($draws), 'Available draws loaded');
    }

    public function placeBet(Request $request)
    {
        $data = $request->validate([
            'bet_number' => 'required|string|max:5',
            'amount' => 'required|numeric|min:1',
            'draw_id' => 'required|exists:draws,id',
            'customer_id' => 'nullable|exists:users,id',
            'is_combination' => 'boolean'
        ]);

        $teller = $request->user();

        try {
            // Check if the draw is still open
            $draw = Draw::findOrFail($data['draw_id']);
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is no longer accepting bets', 422);
            }

            // Start a database transaction
            DB::beginTransaction();

            $ticketId = strtoupper(Str::random(10));

            $bet = Bet::create([
                'bet_number' => $data['bet_number'],
                'amount' => $data['amount'],
                'draw_id' => $data['draw_id'],
                'teller_id' => $teller->id,
                'customer_id' => $data['customer_id'] ?? null,
                'location_id' => $teller->location_id,
                'bet_date' => today(),
                'ticket_id' => $ticketId,
                'is_combination' => $data['is_combination'] ?? false,
                'status' => 'active'
            ]);

            // Here you could add additional related records if needed
            // For example, creating commission records, etc.

            // Commit the transaction
            DB::commit();

            // Load the relationships
            $bet->load(['draw', 'location', 'teller', 'customer']);

            return ApiResponse::success(new BetResource($bet), 'Bet placed successfully');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Return error response
            return ApiResponse::error('Failed to place bet: ' . $e->getMessage(), 500);
        }
    }
}
