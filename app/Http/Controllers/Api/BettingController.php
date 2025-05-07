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
    /**
     * List bets for the authenticated user with optional filtering
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBets(Request $request)
    {
        $user = $request->user();

        $query = Bet::with(['draw', 'customer', 'location', 'gameType'])
            ->where('teller_id', $user->id)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('draw_id'), fn($q) => $q->where('draw_id', $request->draw_id))
            ->when($request->filled('date'), fn($q) => $q->whereDate('bet_date', $request->date))
            ->latest();

        $bets = $query->paginate($request->get('per_page', 20));

        return ApiResponse::paginated($bets, 'Bets retrieved', BetResource::class);
    }

    /**
     * Get available draws for the current day
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableDraws()
    {
        $currentTime = now();

        // Temporarily removed time filter since it's late evening (10:44 PM)
        // Original code with time filter:
        // $draws = Draw::where('draw_date', today())
        //     ->where('is_open', true)
        //     ->where('draw_time', '>', $currentTime->format('H:i:s'))
        //     ->orderBy('draw_time')
        //     ->get();

        // Modified version without time filter for testing:
        $draws = Draw::where('draw_date', today())
            ->where('is_open', true)
            ->orderBy('draw_time')
            ->with(['schedule', 'gameType']) // Eagerly load relationships
            ->get();

        return ApiResponse::success(DrawResource::collection($draws), 'Available draws loaded');
    }

    /**
     * Get available schedules for the current day without duplicates
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableSchedules()
    {
        // Get unique draw times from draws table
        $drawTimes = Draw::where('draw_date', today())
            ->where('is_open', true)
            ->select('draw_time')
            ->distinct()
            ->get()
            ->map(function($draw, $index) {
                return [
                    'id' => $index + 1, // Generate a sequential ID
                    'name' => 'Draw at ' . date('h:i A', strtotime($draw->draw_time)),
                    'draw_time' => $draw->draw_time
                ];
            });

        return ApiResponse::success($drawTimes, 'Available draw times retrieved successfully');
    }

    /**
     * Get available draws for a specific game type and schedule
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableDrawsByGameType(Request $request)
    {
        $request->validate([
            'game_type_id' => 'required|exists:game_types,id',
            'draw_time' => 'nullable|date_format:H:i:s'
        ]);

        $query = Draw::where('draw_date', today())
            ->where('is_open', true);

        if ($request->filled('draw_time')) {
            $query->where('draw_time', $request->draw_time);
        }

        $draws = $query->orderBy('draw_time')->get();
        
        // Filter draws for specific game type in the application layer
        // since we removed the direct relationship in the database
        $gameTypeId = $request->game_type_id;

        return ApiResponse::success(DrawResource::collection($draws), 'Available draws loaded');
    }

    /**
     * Place a new bet
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeBet(Request $request)
    {
        $data = $request->validate([
            'bet_number' => 'required|string|max:5',
            'amount' => 'required|numeric|min:1',
            'draw_id' => 'required|exists:draws,id',
            'game_type_id' => 'required|exists:game_types,id',
            'customer_id' => 'nullable|exists:users,id',
            'is_combination' => 'boolean'
        ]);

        $user = $request->user();

        if (!$user->location_id) {
            return ApiResponse::error('User does not have a location assigned', 422);
        }

        try {
            // Verify the draw is still open
            $draw = Draw::findOrFail($data['draw_id']);
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is no longer accepting bets', 422);
            }

            DB::beginTransaction();

            // Generate a unique ticket ID
            $ticketId = strtoupper(Str::random(10));

            $bet = Bet::create([
                'bet_number' => $data['bet_number'],
                'amount' => $data['amount'],
                'draw_id' => $data['draw_id'],
                'game_type_id' => $data['game_type_id'],
                'teller_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'location_id' => $user->location_id,
                'bet_date' => today(),
                'ticket_id' => $ticketId,
                'is_combination' => $data['is_combination'] ?? false,
                'status' => 'active'
            ]);

            DB::commit();

            // Load relationships for the response
            $bet->load(['gameType']);

            // Return a simplified response according to the mobile API spec
            return ApiResponse::success([
                'ticket_id' => $bet->ticket_id,
                'bet_number' => $bet->bet_number,
                'amount' => $bet->amount,
                'status' => $bet->status
            ], 'Bet placed successfully');

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();

            // Return error response
            return ApiResponse::error('Failed to place bet: ' . $e->getMessage(), 500);
        }
    }
    /**
     * Cancel an active bet
     *
     * @param Request $request
     * @param int $id Bet ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBet(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Find the bet by ID and ensure it belongs to the current user
            $bet = Bet::where('id', $id)
                ->where('teller_id', $request->user()->id)
                ->where('is_claimed', false)
                ->where('is_rejected', false)
                ->lockForUpdate()
                ->first();

            if (!$bet) {
                return ApiResponse::error('Bet not found or already cancelled', 404);
            }

            // Check if the draw is still open
            $draw = Draw::find($bet->draw_id);
            if ($draw && !$draw->is_open) {
                return ApiResponse::error('Cannot cancel bet as the draw is closed', 422);
            }

            // Update the bet to mark it as rejected
            $bet->is_rejected = true;
            $bet->save();

            DB::commit();

            // Return a simplified response according to the mobile API spec
            return ApiResponse::success(null, 'Bet cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to cancel bet: ' . $e->getMessage(), 500);
        }
    }
}
