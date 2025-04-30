<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Claim;
use App\Models\Result;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClaimResource;

class ClaimController extends Controller
{
    /**
     * Submit a new claim for a winning bet
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => 'required|string|exists:bets,ticket_id',
            'result_id' => 'required|exists:results,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Use lockForUpdate to prevent race conditions
            $bet = Bet::where('ticket_id', $data['ticket_id'])
                ->where('status', 'won')
                ->lockForUpdate()
                ->first();

            if (!$bet) {
                return ApiResponse::error('Ticket is not valid or not marked as winning.', 404);
            }

            // Prevent double claim
            if ($bet->claim) {
                return ApiResponse::error('Ticket already claimed.', 422);
            }

            // Verify the result exists and matches the bet
            $result = Result::findOrFail($data['result_id']);
            
            // Verify the result matches the bet's draw type
            if ($bet->draw && $bet->draw->type !== $result->type) {
                return ApiResponse::error('Result type does not match the bet draw type.', 422);
            }

            // Calculate payout based on bet type and amount
            $payoutMultiplier = $this->calculatePayoutMultiplier($bet);
            $commissionRate = 0.05; // 5% commission
            
            $claim = Claim::create([
                'bet_id' => $bet->id,
                'teller_id' => $request->user()->id,
                'amount' => $bet->amount * $payoutMultiplier,
                'commission_amount' => $bet->amount * $commissionRate,
                'claimed_at' => now(),
                'qr_code_data' => $bet->ticket_id,
            ]);

            // Update bet status
            $bet->status = 'claimed';
            $bet->save();
            
            // Load relationships for response
            $claim->load(['bet', 'teller']);
            
            DB::commit();

            return ApiResponse::success(new ClaimResource($claim), 'Claim processed successfully');
        } catch (\Exception $e) {
            return $this->handleClaimException($e);
        }
    }

    /**
     * List claims for the authenticated teller
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Claim::where('teller_id', $user->id)
            ->with(['bet.draw', 'bet.customer', 'teller'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('bet', fn($sub) =>
                    $sub->where('ticket_id', 'like', '%' . $request->search . '%')
                        ->orWhere('bet_number', 'like', '%' . $request->search . '%')
                );
            })
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('claimed_at', $request->date)
            )
            ->when($request->filled('amount_min'), fn($q) =>
                $q->where('amount', '>=', $request->amount_min)
            )
            ->when($request->filled('amount_max'), fn($q) =>
                $q->where('amount', '<=', $request->amount_max)
            )
            ->latest('claimed_at');

        $claims = $query->paginate($request->get('per_page', 20));

        return ApiResponse::paginated($claims, 'Claims retrieved', ClaimResource::class);
    }
    
    /**
     * Calculate payout multiplier based on bet type
     *
     * @param Bet $bet
     * @return float
     */
    private function calculatePayoutMultiplier(Bet $bet)
    {
        // Default multiplier
        $multiplier = 2;
        
        // Adjust multiplier based on bet type and combination
        if ($bet->draw) {
            switch ($bet->draw->type) {
                case 'S2': // Swertres 2 digits
                    $multiplier = $bet->is_combination ? 1.5 : 2;
                    break;
                case 'S3': // Swertres 3 digits
                    $multiplier = $bet->is_combination ? 2 : 3;
                    break;
                case 'D4': // Digit 4
                    $multiplier = $bet->is_combination ? 2.5 : 4;
                    break;
            }
        }
        
        return $multiplier;
    }
    
    /**
     * Catch any exceptions during claim processing
     */
    private function handleClaimException(\Exception $e)
    {
        DB::rollBack();
        return ApiResponse::error('Failed to process claim: ' . $e->getMessage(), 500);
    }
}
