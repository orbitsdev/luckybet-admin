<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\Receipt;
use App\Models\BetRatio;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;
use App\Models\LowWinNumber;
use Illuminate\Http\Request;
use App\Models\WinningAmount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\BetResource;
use App\Http\Resources\ReceiptResource;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    /**
     * Get or create a draft receipt for the current teller
     */
    public function getDraft(Request $request)
    {
        $user = $request->user();
        
        if (!$user->location_id) {
            return ApiResponse::error('User does not have a location assigned', 422);
        }
        
        // Find existing draft or create a new one
        $receipt = Receipt::where('teller_id', $user->id)
            ->where('status', 'draft')
            ->first();
            
        if (!$receipt) {
            $receipt = Receipt::create([
                'teller_id' => $user->id,
                'location_id' => $user->location_id,
                'status' => 'draft',
            ]);
        }
        
        // Load relationships
        $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
        
        return ApiResponse::success(new ReceiptResource($receipt));
    }
    
    /**
     * Get a specific receipt with all details
     */
    public function show(Receipt $receipt, Request $request)
    {
        $user = $request->user();
        
        // Check if the receipt belongs to this teller or user is admin/coordinator
        if ($user->role !== 'admin' && $user->role !== 'coordinator' && $receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
        
        return ApiResponse::success(new ReceiptResource($receipt));
    }
    
    /**
     * List receipts with pagination
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'status' => 'sometimes|string|in:draft,placed,cancelled',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date',
            'date' => 'sometimes|date',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        
        $perPage = $validated['per_page'] ?? 15;
        
        $query = Receipt::query()
            ->with(['teller', 'location'])
            ->orderBy('created_at', 'desc');
            
        // If not admin/coordinator, only show own receipts
        if ($user->role !== 'admin' && $user->role !== 'coordinator') {
            $query->where('teller_id', $user->id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by single date if provided, or use today's date if no date filters are provided
        if ($request->filled('date')) {
            $query->whereDate('receipt_date', $request->date);
        } 
        // Filter by date range if provided
        elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('receipt_date', [$request->from_date, $request->to_date]);
        }
        // Default to today if no date filter is provided
        else {
            $query->whereDate('receipt_date', Carbon::today()->format('Y-m-d'));
        }
        
        $receipts = $query->paginate($perPage);
        
        return ApiResponse::paginated(
            $receipts, 
            'Receipts retrieved',
            ReceiptResource::class
        );
    }
    
    /**
     * Add a bet to a receipt
     */
    public function addBet(Receipt $receipt, Request $request)
    {
        $user = $request->user();
        
        // Check if receipt belongs to this teller
        if ($receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        // Check if receipt is still in draft status
        if ($receipt->status !== 'draft') {
            return ApiResponse::error('This receipt has already been finalized or cancelled', 422);
        }
        
        // Validate bet data
        $data = $request->validate([
            'bet_number' => 'required|string|max:5',
            'amount' => 'required|numeric|min:1',
            'draw_id' => 'required|exists:draws,id',
            'game_type_id' => 'required|exists:game_types,id',
            'customer_id' => 'nullable|exists:users,id',
            'is_combination' => 'boolean',
            'd4_sub_selection' => 'nullable|in:S2,S3'
        ]);
        
        // Check if user has location
        if (!$user->location_id) {
            return ApiResponse::error('User does not have a location assigned', 422);
        }
        
        try {
            // Check if draw is open
            $draw = Draw::findOrFail($data['draw_id']);
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is no longer accepting bets', 422);
            }
            
            DB::beginTransaction();
            
            // BET RATIO (CAP/SOLD OUT) CHECK
            $totalBetForNumber = Bet::where('draw_id', $data['draw_id'])
                ->where('game_type_id', $data['game_type_id'])
                ->where('location_id', $user->location_id)
                ->where('bet_number', $data['bet_number'])
                ->sum('amount');
                
            $cap = BetRatio::where('draw_id', $data['draw_id'])
                ->where('game_type_id', $data['game_type_id'])
                ->where('location_id', $user->location_id)
                ->where('bet_number', $data['bet_number'])
                ->when(isset($data['d4_sub_selection']), function ($query) use ($data) {
                    // If this is a D4 subtype bet, check for sold out with matching subtype
                    return $query->where(function ($q) use ($data) {
                        $q->where('sub_selection', $data['d4_sub_selection'])
                          ->orWhereNull('sub_selection');
                    });
                })
                ->value('max_amount');
                
            if ($cap !== null && ($totalBetForNumber + $data['amount']) > $cap) {
                DB::rollBack();
                return ApiResponse::error('Sold Out', 422);
            }
            
            // LOW WIN OVERRIDE / WINNING AMOUNT LOGIC
            $lowWin = LowWinNumber::where('draw_id', $data['draw_id'])
                ->where('game_type_id', $data['game_type_id'])
                ->where('location_id', $user->location_id)
                ->where('bet_number', $data['bet_number'])
                ->first();
                
            // Fallback: try global low win (bet_number is null or empty)
            if (!$lowWin) {
                $lowWin = LowWinNumber::where('draw_id', $data['draw_id'])
                    ->where('game_type_id', $data['game_type_id'])
                    ->where('location_id', $user->location_id)
                    ->where(function ($query) {
                        $query->whereNull('bet_number')->orWhere('bet_number', '');
                    })
                    ->first();
            }
            
            $winningAmount = $lowWin
                ? $lowWin->winning_amount
                : (WinningAmount::where('game_type_id', $data['game_type_id'])
                    ->where('location_id', $user->location_id)
                    ->where('amount', $data['amount'])
                    ->value('winning_amount'));
                    
            if (is_null($winningAmount)) {
                DB::rollBack();
                return ApiResponse::error(
                    'Winning amount is not set for this game type and amount. Please contact admin.',
                    422
                );
            }
            
            // COMMISSION LOGIC
            $commissionRate = $user->commission->rate ?? 0.15; // default 15% if not set
            $commissionAmount = $data['amount'] * $commissionRate;
            
            // Create the bet and associate with receipt
            $bet = Bet::create([
                'bet_number'       => $data['bet_number'],
                'amount'           => $data['amount'],
                'winning_amount'   => $winningAmount,
                'draw_id'          => $data['draw_id'],
                'game_type_id'     => $data['game_type_id'],
                'teller_id'        => $user->id,
                'customer_id'      => $data['customer_id'] ?? null,
                'location_id'      => $user->location_id,
                'bet_date'         => today(),
                'ticket_id'        => null, // Will be set when receipt is finalized
                'is_combination'   => $data['is_combination'] ?? false,
                'd4_sub_selection' => $data['d4_sub_selection'] ?? null,
                'commission_rate'  => $commissionRate,
                'commission_amount'=> $commissionAmount,
                'receipt_id'       => $receipt->id,
            ]);
            
            // Update receipt total amount
            $receipt->total_amount = $receipt->calculateTotalAmount();
            $receipt->save();
            
            DB::commit();
            
            // Load relationships for response
            $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
            
            return ApiResponse::success(
                new ReceiptResource($receipt),
                'Bet added to receipt successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to add bet: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Remove a bet from a receipt
     */
    public function removeBet(Receipt $receipt, Bet $bet, Request $request)
    {
        $user = $request->user();
        
        // Check if receipt belongs to this teller
        if ($receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        // Check if receipt is still in draft status
        if ($receipt->status !== 'draft') {
            return ApiResponse::error('This receipt has already been finalized or cancelled', 422);
        }
        
        // Check if bet belongs to this receipt
        if ($bet->receipt_id !== $receipt->id) {
            return ApiResponse::error('This bet does not belong to the specified receipt', 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete the bet
            $bet->delete();
            
            // Update receipt total amount
            $receipt->total_amount = $receipt->calculateTotalAmount();
            $receipt->save();
            
            DB::commit();
            
            // Load relationships for response
            $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
            
            return ApiResponse::success(
                new ReceiptResource($receipt),
                'Bet removed from receipt successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to remove bet: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update a bet in a receipt (optional feature)
     */
    public function updateBet(Receipt $receipt, Bet $bet, Request $request)
    {
        $user = $request->user();
        
        // Check if receipt belongs to this teller
        if ($receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        // Check if receipt is still in draft status
        if ($receipt->status !== 'draft') {
            return ApiResponse::error('This receipt has already been finalized or cancelled', 422);
        }
        
        // Check if bet belongs to this receipt
        if ($bet->receipt_id !== $receipt->id) {
            return ApiResponse::error('This bet does not belong to the specified receipt', 422);
        }
        
        // Validate bet data
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'is_combination' => 'boolean',
            'd4_sub_selection' => 'nullable|in:S2,S3'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update only allowed fields
            $bet->amount = $data['amount'];
            $bet->is_combination = $data['is_combination'] ?? $bet->is_combination;
            $bet->d4_sub_selection = $data['d4_sub_selection'] ?? $bet->d4_sub_selection;
            
            // Recalculate commission
            $commissionRate = $user->commission->rate ?? 0.15;
            $bet->commission_rate = $commissionRate;
            $bet->commission_amount = $data['amount'] * $commissionRate;
            
            // Get winning amount
            $lowWin = LowWinNumber::where('draw_id', $bet->draw_id)
                ->where('game_type_id', $bet->game_type_id)
                ->where('location_id', $user->location_id)
                ->where('bet_number', $bet->bet_number)
                ->first();
                
            if (!$lowWin) {
                $lowWin = LowWinNumber::where('draw_id', $bet->draw_id)
                    ->where('game_type_id', $bet->game_type_id)
                    ->where('location_id', $user->location_id)
                    ->where(function ($query) {
                        $query->whereNull('bet_number')->orWhere('bet_number', '');
                    })
                    ->first();
            }
            
            $winningAmount = $lowWin
                ? $lowWin->winning_amount
                : (WinningAmount::where('game_type_id', $bet->game_type_id)
                    ->where('location_id', $user->location_id)
                    ->where('amount', $data['amount'])
                    ->value('winning_amount'));
                    
            if (is_null($winningAmount)) {
                DB::rollBack();
                return ApiResponse::error(
                    'Winning amount is not set for this game type and amount. Please contact admin.',
                    422
                );
            }
            
            $bet->winning_amount = $winningAmount;
            $bet->save();
            
            // Update receipt total amount
            $receipt->total_amount = $receipt->calculateTotalAmount();
            $receipt->save();
            
            DB::commit();
            
            // Load relationships for response
            $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
            
            return ApiResponse::success(
                new ReceiptResource($receipt),
                'Bet updated successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to update bet: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Finalize/place a receipt
     */
    public function placeReceipt(Receipt $receipt, Request $request)
    {
        $user = $request->user();
        
        // Check if receipt belongs to this teller
        if ($receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        // Check if receipt is still in draft status
        if ($receipt->status !== 'draft') {
            return ApiResponse::error('This receipt has already been finalized or cancelled', 422);
        }
        
        // Check if receipt has any bets
        if ($receipt->bets()->count() === 0) {
            return ApiResponse::error('Cannot finalize an empty receipt', 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Generate a ticket ID for the receipt
            $receipt->status = 'placed';
            $receipt->receipt_date = today();
            $receipt->total_amount = $receipt->calculateTotalAmount();
            $receipt->save(); // This will trigger the observer to generate ticket_id
            
            // Update all bets to have the same ticket_id
            $receipt->bets()->update([
                'ticket_id' => $receipt->ticket_id
            ]);
            
            DB::commit();
            
            // Load relationships for response
            $receipt->load(['bets.gameType', 'bets.draw', 'teller', 'location']);
            
            return ApiResponse::success(
                new ReceiptResource($receipt),
                'Receipt finalized successfully'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to finalize receipt: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Cancel a draft receipt
     */
    public function cancelReceipt(Receipt $receipt, Request $request)
    {
        $user = $request->user();
        
        // Check if receipt belongs to this teller
        if ($receipt->teller_id !== $user->id) {
            return ApiResponse::error('Unauthorized', 403);
        }
        
        // Check if receipt is still in draft status
        if ($receipt->status !== 'draft') {
            return ApiResponse::error('This receipt has already been finalized or cancelled', 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete all bets associated with this receipt
            $receipt->bets()->delete();
            
            // Mark receipt as cancelled
            $receipt->status = 'cancelled';
            $receipt->save();
            
            DB::commit();
            
            return ApiResponse::success(null, 'Receipt cancelled successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to cancel receipt: ' . $e->getMessage(), 500);
        }
    }
}
