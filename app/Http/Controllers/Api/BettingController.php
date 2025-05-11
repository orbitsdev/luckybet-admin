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
            ->orderBy('draw_time')
            ->get();

        return ApiResponse::success(DrawResource::collection($draws));
    }

   
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
                'teller_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'location_id' => $user->location_id,
                'bet_date' => today(),
                'ticket_id' => $ticketId,
                'is_combination' => $data['is_combination'] ?? false
            ]);

        DB::commit();

       
            $bet->load(['gameType']);

          
            return ApiResponse::success(new BetResource($bet), 'Bet placed successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            return ApiResponse::error('Failed to place bet: ' . $e->getMessage(), 500);
        }
    }

 
    public function listBets(Request $request)
    {
        $user = $request->user();
    
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'all' => 'sometimes|boolean',
            'draw_id' => 'sometimes|integer|exists:draws,id',
            'date' => 'sometimes|date',
            'is_rejected' => 'sometimes|boolean',
            'is_claimed' => 'sometimes|boolean',
        ]);
        $perPage = $validated['per_page'] ?? 20;
    
        $query = Bet::with(['draw', 'customer', 'location', 'gameType'])
            ->where('teller_id', $user->id)
           
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
           
            ->when($request->filled('draw_id'), fn($q) => $q->where('draw_id', $request->draw_id))
            ->when($request->filled('date'), fn($q) => $q->whereDate('bet_date', $request->date))
            ->when($request->filled('is_rejected'), fn($q) => $q->where('is_rejected', $request->boolean('is_rejected')))
            ->when($request->filled('is_claimed'), fn($q) => $q->where('is_claimed', $request->boolean('is_claimed')))
            ->latest();
    
        if ($request->boolean('all', false)) {
           
            $bets = $query->limit(1000)->get();
            return ApiResponse::success(BetResource::collection($bets), 'All bets retrieved');
        } else {
            $bets = $query->paginate($perPage);
            return ApiResponse::paginated($bets, 'Bets retrieved', BetResource::class);
        }
    }

 
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
    public function listCancelledBets(Request $request)
    {
        $user = $request->user();
        $query = Bet::with(['draw', 'customer', 'location', 'gameType'])
            ->where('teller_id', $user->id)
            ->where('is_rejected', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
            ->latest();

        $bets = $query->get();
        return ApiResponse::success(BetResource::collection($bets), 'Cancelled bets retrieved');
    }


}