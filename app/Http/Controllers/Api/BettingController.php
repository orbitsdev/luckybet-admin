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
use Carbon\Carbon;

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
            'is_rejected' => 'sometimes|string|in:true,false,0,1',
            'is_claimed' => 'sometimes|string|in:true,false,0,1',
            'game_type_id' => 'sometimes|integer|exists:game_types,id',
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

            // Check if the draw is closed (can only claim after draw is closed)
            $draw = Draw::find($bet->draw_id);
            if ($draw && $draw->is_open) {
                return ApiResponse::error('Cannot claim bet as the draw is still open', 422);
            }

            // Mark the bet as claimed
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


    //list claimed bets
    /**
     * List claimed bets (bets that have been marked as claimed)
     */
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
                // Filter by the specified date
                $q->whereDate('claimed_at', $request->date);
            }, function($q) {
                // Default to today if no date specified
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
                
                // For paginated results, we need to filter the items collection
                $filteredItems = $bets->getCollection()->filter(function($bet) use ($isWinner) {
                    return $bet->is_winner === $isWinner;
                });
                
                // Replace the items in the paginator with our filtered collection
                $bets->setCollection($filteredItems);
            }
            
            return ApiResponse::paginated($bets, 'Claimed bets retrieved', BetResource::class);
        }
    }
    
    /**
     * List hit bets (winning bets regardless of claim status)
     * This method uses a more efficient approach to identify winning bets directly from results
     */
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

        // Use a more efficient query to find winning bets directly
        $query = Bet::with(['draw', 'customer', 'location', 'gameType', 'draw.result'])
            ->where('teller_id', $user->id)
            ->where('is_rejected', false)
            ->whereDate('bet_date', $date)
            ->whereHas('draw', function($q) {
                // Only include bets for draws that have results
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

        // For better performance, let's limit the query results before filtering
        // This helps with memory usage when there are many bets
        if ($request->boolean('all', false)) {
            // If requesting all results, still apply a reasonable limit
            $allBets = $query->limit(1000)->get();
            
            // Filter to only include winning bets using the isHit method
            $winningBets = $allBets->filter(function($bet) {
                return $bet->isHit();
            });
            
            return ApiResponse::success(BetResource::collection($winningBets), 'All winning bets retrieved');
        } else {
            // For paginated results, get more than we need to account for filtering
            // This helps ensure we have enough items after filtering
            $multiplier = 3; // Get 3x the items we need, assuming ~33% win rate
            $extendedLimit = $perPage * $multiplier;
            
            $page = $request->input('page', 1);
            $offset = ($page - 1) * $perPage;
            
            // Get a batch of potential winners
            $potentialBets = $query->skip($offset)->limit($extendedLimit)->get();
            
            // Filter to winners only
            $winningBets = $potentialBets->filter(function($bet) {
                return $bet->isHit();
            });
            
            // If we don't have enough winners, fetch more
            if ($winningBets->count() < $perPage && $potentialBets->count() >= $extendedLimit) {
                // We hit our limit but didn't get enough winners, so fetch more
                $additionalBets = $query->skip($offset + $extendedLimit)->limit($extendedLimit)->get();
                
                $additionalWinners = $additionalBets->filter(function($bet) {
                    return $bet->isHit();
                });
                
                // Merge the winners
                $winningBets = $winningBets->merge($additionalWinners);
            }
            
            // Take just what we need for this page
            $paginatedBets = $winningBets->take($perPage)->values();
            
            // Get total count for accurate pagination (only if on first page)
            $totalCount = $paginatedBets->count();
            if ($page == 1 && $paginatedBets->count() >= $perPage) {
                // If we're on page 1 and have a full page, we need to count the total
                $totalCount = $query->count();
                
                // Estimate the total winners based on our sample
                $winRate = $paginatedBets->count() / $potentialBets->count();
                $estimatedTotal = ceil($totalCount * $winRate);
                $totalCount = $estimatedTotal;
            }
            
            // Create paginator
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
