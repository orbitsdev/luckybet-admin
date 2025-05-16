<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;

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
            'is_combination' => 'boolean',
            'd4_sub_selection' => 'nullable|in:S2,S3'
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
                'is_combination' => $data['is_combination'] ?? false,
                'd4_sub_selection' => $data['d4_sub_selection'] ?? null
            ]);

        DB::commit();


            $bet->load(['draw', 'customer', 'location', 'gameType']);


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
            'is_rejected' => 'sometimes|string|in:true,false,0,1',
            'is_claimed' => 'sometimes|string|in:true,false,0,1',
            'game_type_id' => 'sometimes|integer|exists:game_types,id',
        ]);
        $perPage = $validated['per_page'] ?? 20;


        $date = $request->filled('date') ? $request->date : Carbon::today()->format('Y-m-d');
        
        $query = Bet::with(['draw', 'customer', 'location', 'gameType'])
            ->where('teller_id', $user->id)
            ->whereDate('bet_date', $date)

            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })

            ->when($request->filled('draw_id'), fn($q) => $q->where('draw_id', $request->draw_id))
            ->when($request->filled('game_type_id'), fn($q) => $q->where('game_type_id', $request->game_type_id))
            ->when($request->filled('is_rejected'), function($q) use ($request) {
                $value = filter_var($request->is_rejected, FILTER_VALIDATE_BOOLEAN);
                $q->where('is_rejected', $value);
            })
            ->when($request->filled('is_claimed'), function($q) use ($request) {
                $value = filter_var($request->is_claimed, FILTER_VALIDATE_BOOLEAN);
                $q->where('is_claimed', $value);
            })
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


            $bet->is_rejected = true;
            $bet->save();

            DB::commit();


            return ApiResponse::success(null, 'Bet cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to cancel bet: ' . $e->getMessage(), 500);
        }
    }

    public function listCancelledBets(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'date' => 'sometimes|date',
            'draw_id' => 'sometimes|integer|exists:draws,id',
            'search' => 'sometimes|string',
            'game_type_id' => 'sometimes|integer|exists:game_types,id',
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $query = Bet::with(['draw', 'customer', 'location', 'gameType'])
            ->where('teller_id', $user->id)
            ->where('is_rejected', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('date'), function($q) use ($request) {
                $date = Carbon::parse($request->date)->startOfDay();
                $q->whereHas('draw', function($query) use ($date) {
                    $query->whereDate('draw_date', $date);
                });
            })
            ->when($request->filled('draw_id'), function($q) use ($request) {
                $q->where('draw_id', $request->draw_id);
            })
            ->when($request->filled('game_type_id'), function($q) use ($request) {
                $q->where('game_type_id', $request->game_type_id);
            })
            ->latest();

        $bets = $query->paginate($perPage);
        return ApiResponse::paginated($bets, 'Cancelled bets retrieved', BetResource::class);
    }




    public function cancelBetByTicketId(Request $request, $ticket_id)
    {
        try {
            DB::beginTransaction();

            // Find the bet by ticket_id and ensure it belongs to the current user
            $bet = Bet::where('ticket_id', $ticket_id)
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

            $bet->is_rejected = true;
            $bet->save();

            DB::commit();

            return ApiResponse::success(null, 'Bet cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to cancel bet: ' . $e->getMessage(), 500);
        }
    }


    public function claimByTicketId(Request $request, $ticket_id)
    {
        try {
            DB::beginTransaction();

            // Find the bet by ticket_id and ensure it belongs to the current user
            $bet = Bet::where('ticket_id', $ticket_id)
                ->where('teller_id', $request->user()->id)
                ->where('is_claimed', false)
                ->where('is_rejected', false)
                ->lockForUpdate()
                ->first();

            if (!$bet) {
                return ApiResponse::error('Bet not found or already claimed/cancelled', 404);
            }


            $draw = Draw::find($bet->draw_id);
            if ($draw && $draw->is_open) {
                return ApiResponse::error('Cannot claim bet as the draw is still open', 422);
            }


            $bet->is_claimed = true;
            $bet->claimed_at = now();
            $bet->save();

            DB::commit();

            return ApiResponse::success(null, 'Bet claimed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to claim bet: ' . $e->getMessage(), 500);
        }
    }



    public function listClaimedBets(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'all' => 'sometimes|boolean',
            'date' => 'sometimes|date',
            'draw_id' => 'sometimes|integer|exists:draws,id',
            'search' => 'sometimes|string',
            'game_type_id' => 'sometimes|integer|exists:game_types,id',
            'is_winner' => 'sometimes|string|in:true,false,0,1',
        ]);

        $perPage = $validated['per_page'] ?? 20;

        // We need to eager load results for is_winner calculation
        $query = Bet::with(['draw', 'customer', 'location', 'gameType', 'draw.result'])
            ->where('teller_id', $user->id)
            ->where('is_claimed', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('date'), function($q) use ($request) {

                $q->whereDate('claimed_at', $request->date);
            }, function($q) {

                $q->whereDate('claimed_at', today()->toDateString());
            })
            ->when($request->filled('draw_id'), function($q) use ($request) {
                $q->where('draw_id', $request->draw_id);
            })
            ->when($request->filled('game_type_id'), function($q) use ($request) {
                $q->where('game_type_id', $request->game_type_id);
            })
            ->latest();

        // Get the results
        if ($request->boolean('all', false)) {
            $bets = $query->limit(1000)->get();
            
            // Filter by is_winner if requested
            if ($request->filled('is_winner')) {
                $isWinner = in_array($request->is_winner, ['true', '1']);
                $bets = $bets->filter(function($bet) use ($isWinner) {
                    return $bet->is_winner === $isWinner;
                });
            }
            
            return ApiResponse::success(BetResource::collection($bets), 'All claimed bets retrieved');
        } else {
            $bets = $query->paginate($perPage);
            
            // Filter by is_winner if requested
            if ($request->filled('is_winner')) {
                $isWinner = in_array($request->is_winner, ['true', '1']);
                
    
                $filteredItems = $bets->getCollection()->filter(function($bet) use ($isWinner) {
                    return $bet->is_winner === $isWinner;
                });
                
    
                $bets->setCollection($filteredItems);
            }
            
            return ApiResponse::paginated($bets, 'Claimed bets retrieved', BetResource::class);
        }
    }
    

    public function listHitBets(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'all' => 'sometimes|boolean',
            'date' => 'sometimes|date',
            'draw_id' => 'sometimes|integer|exists:draws,id',
            'search' => 'sometimes|string',
            'game_type_id' => 'sometimes|integer|exists:game_types,id',
            'is_claimed' => 'sometimes|string|in:true,false,0,1',
        ]);

        $perPage = $validated['per_page'] ?? 20;
        $date = $request->filled('date') ? $request->date : today()->toDateString();


        $query = Bet::with(['draw', 'customer', 'location', 'gameType', 'draw.result'])
            ->where('teller_id', $user->id)
            ->where('is_rejected', false)
            ->whereDate('bet_date', $date)
            ->whereHas('draw', function($q) {

                $q->whereHas('result');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('draw_id'), function($q) use ($request) {
                $q->where('draw_id', $request->draw_id);
            })
            ->when($request->filled('game_type_id'), function($q) use ($request) {
                $q->where('game_type_id', $request->game_type_id);
            })
            ->when($request->filled('is_claimed'), function($q) use ($request) {
                $isClaimed = in_array($request->is_claimed, ['true', '1']);
                $q->where('is_claimed', $isClaimed);
            })
            ->latest();


        if ($request->boolean('all', false)) {

            $allBets = $query->limit(1000)->get();
            

            $winningBets = $allBets->filter(function($bet) {
                return $bet->isHit();
            });
            
            return ApiResponse::success(BetResource::collection($winningBets), 'All winning bets retrieved');
        } else {

            $multiplier = 3;
            $extendedLimit = $perPage * $multiplier;
            
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
            

            $potentialBets = $query->skip($offset)->limit($extendedLimit)->get();
            

            $winningBets = $potentialBets->filter(function($bet) {
                return $bet->isHit();
            });
            

            if ($winningBets->count() < $perPage && $potentialBets->count() >= $extendedLimit) {

                $additionalBets = $query->skip($offset + $extendedLimit)->limit($extendedLimit)->get();
                
                $additionalWinners = $additionalBets->filter(function($bet) {
                    return $bet->isHit();
                });
                

                $winningBets = $winningBets->merge($additionalWinners);
            }
            

            $paginatedBets = $winningBets->take($perPage)->values();
            

            $totalCount = $paginatedBets->count();
            if ($page == 1 && $paginatedBets->count() >= $perPage) {

                $totalCount = $query->count();
                

                $winRate = $paginatedBets->count() / $potentialBets->count();
                $estimatedTotal = ceil($totalCount * $winRate);
                $totalCount = $estimatedTotal;
            }
            

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedBets,
                $totalCount,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
            return ApiResponse::paginated($paginator, 'Winning bets retrieved', BetResource::class);
        }
    }

}
