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
     * List bets for the authenticated teller with optional filtering
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listBets(Request $request)
    {
        $user = $request->user();

        $query = Bet::with(['draw', 'customer', 'location'])
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
        $currentTime = now();
        
        // Get unique schedules from draws table
        $schedules = Draw::where('draw_date', today())
            ->where('is_open', true)
            ->select('schedule_id')
            ->distinct()
            ->with('schedule')
            ->get()
            ->map(function($draw) {
                return [
                    'id' => $draw->schedule->id,
                    'name' => $draw->schedule->name,
                    'draw_time' => $draw->schedule->draw_time,
                    'formatted_time' => date('g:i A', strtotime($draw->schedule->draw_time)) // Format as 2:00 PM
                ];
            });
            
        return ApiResponse::success($schedules, 'Available schedules loaded');
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
            'schedule_id' => 'nullable|exists:schedules,id'
        ]);
        
        $query = Draw::where('draw_date', today())
            ->where('is_open', true)
            ->where('game_type_id', $request->game_type_id)
            ->with(['schedule', 'gameType']);
            
        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }
        
        $draws = $query->orderBy('draw_time')->get();
        
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

        $teller = $request->user();

        if (!$teller->location_id) {
            return ApiResponse::error('Teller does not have a location assigned', 422);
        }

        try {
                
            $draw = Draw::findOrFail($data['draw_id']);
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is no longer accepting bets', 422);
            }

            DB::beginTransaction();

            $ticketId = strtoupper(Str::random(10));

            $bet = Bet::create([
                'bet_number' => $data['bet_number'],
                'amount' => $data['amount'],
                'draw_id' => $data['draw_id'],
                'game_type_id' => $data['game_type_id'],
                'teller_id' => $teller->id,
                'customer_id' => $data['customer_id'] ?? null,
                'location_id' => $teller->location_id,
                'bet_date' => today(),
                'ticket_id' => $ticketId,
                'is_combination' => $data['is_combination'] ?? false,
                'status' => 'active'
            ]);

            DB::commit();

            $bet->load(['draw', 'location', 'teller', 'customer']);

            return ApiResponse::success(new BetResource($bet), 'Bet placed successfully');

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelBet(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|string|exists:bets,ticket_id',
        ]);

        try {

            DB::beginTransaction();

            $bet = Bet::where('ticket_id', $request->ticket_id)
                ->where('teller_id', $request->user()->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$bet) {
                return ApiResponse::error('Bet not found or already cancelled', 404);
            }


            $draw = Draw::find($bet->draw_id);
            if ($draw && !$draw->is_open) {
                return ApiResponse::error('Cannot cancel bet as the draw is closed', 422);
            }

            $bet->status = 'cancelled';
            $bet->save();


            DB::commit();


            $bet->load(['draw', 'location', 'teller', 'customer']);

            return ApiResponse::success(new BetResource($bet), 'Bet cancelled successfully');

        } catch (\Exception $e) {

            DB::rollBack();


            return ApiResponse::error('Failed to cancel bet: ' . $e->getMessage(), 500);
        }
    }
}
