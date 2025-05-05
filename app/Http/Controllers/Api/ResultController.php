<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\Result;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;

class ResultController extends Controller
{
    /**
     * Submit a new result for a draw
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $data = $request->validate([
            'draw_id' => 'required|exists:draws,id',
            's2_winning_number' => 'required|string|max:2|regex:/^\d{2}$/',
            's3_winning_number' => 'required|string|max:3|regex:/^\d{3}$/',
            'd4_winning_number' => 'required|string|max:4|regex:/^\d{4}$/',
        ]);

        try {
            DB::beginTransaction();
            
            // Find the draw
            $draw = Draw::findOrFail($data['draw_id']);
            
            // Check if the draw is still open
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is already closed and has results.', 422);
            }
            
            // Prevent re-encoding of the result
            $existing = Result::where('draw_date', $draw->draw_date)
                             ->where('draw_time', $draw->draw_time)
                             ->first();
    
            if ($existing) {
                return ApiResponse::error('Result already submitted for this draw.', 409);
            }
            
            // Create the result
            $result = Result::create([
                'draw_id' => $draw->id,
                'draw_date' => $draw->draw_date,
                'draw_time' => $draw->draw_time,
                's2_winning_number' => $data['s2_winning_number'],
                's3_winning_number' => $data['s3_winning_number'],
                'd4_winning_number' => $data['d4_winning_number'],
                'coordinator_id' => $request->user()->id,
            ]);
            
            // Update bet statuses based on the winning numbers
            $this->updateBetStatuses($draw->id, $data['s2_winning_number'], $data['s3_winning_number'], $data['d4_winning_number']);
            
            DB::commit();
    
            return ApiResponse::success(new ResultResource($result), 'Result submitted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to submit result: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a list of results
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validate date parameter if provided
        if ($request->filled('date')) {
            $request->validate([
                'date' => 'date_format:Y-m-d'
            ]);
        }
        
        $query = Result::with(['coordinator:id,name,username', 'draw'])
            ->select('id', 'draw_id', 'draw_date', 'winning_number', 'coordinator_id', 'created_at')
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('draw_date', $request->date)
            )
            ->when($request->filled('type'), function($q) use ($request) {
                return $q->whereHas('draw', function($query) use ($request) {
                    $query->where('type', $request->type);
                });
            })
            ->when($request->filled('search'), fn($q) =>
                $q->where('winning_number', 'like', '%' . $request->search . '%')
            );
        
        // Get the latest results first
        $results = $query->orderBy('draw_date', 'desc')
                         ->paginate($request->get('per_page', 20));

        return ApiResponse::paginated($results, 'Results loaded', ResultResource::class);
    }
    
    /**
     * Validate winning number format based on draw type
     *
     * @param string $drawType
     * @param string $winningNumber
     * @return bool
     */
    private function validateWinningNumber($drawType, $winningNumber)
    {
        // Get the draw type from the database
        switch ($drawType) {
            case 'S2': // Swertres 2 digits
                return preg_match('/^\d{2}$/', $winningNumber);
            case 'S3': // Swertres 3 digits
                return preg_match('/^\d{3}$/', $winningNumber);
            case 'D4': // Digit 4
                return preg_match('/^\d{4}$/', $winningNumber);
            default:
                return true; // Allow other formats for custom draw types
        }
    }
    
    /**
     * Update bet statuses based on the winning numbers for each game type
     *
     * @param int $drawId
     * @param string $s2WinningNumber
     * @param string $s3WinningNumber
     * @param string $d4WinningNumber
     * @return void
     */
    private function updateBetStatuses($drawId, $s2WinningNumber, $s3WinningNumber, $d4WinningNumber)
    {
        // Mark all active bets for this draw as lost by default
        Bet::where('draw_id', $drawId)
           ->where('status', 'active')
           ->update(['status' => 'lost']);
        
        // Process S2 bets
        $this->processGameTypeBets($drawId, 'S2', $s2WinningNumber);
        
        // Process S3 bets
        $this->processGameTypeBets($drawId, 'S3', $s3WinningNumber);
        
        // Process D4 bets
        $this->processGameTypeBets($drawId, 'D4', $d4WinningNumber);
    }
    
    /**
     * Process bets for a specific game type
     *
     * @param int $drawId
     * @param string $gameType
     * @param string $winningNumber
     * @return void
     */
    private function processGameTypeBets($drawId, $gameType, $winningNumber)
    {
        // Mark winning bets for this game type
        Bet::where('draw_id', $drawId)
           ->where('game_type', $gameType)
           ->where('status', 'lost')
           ->where(function($query) use ($winningNumber) {
                // Exact match
                $query->where('bet_number', $winningNumber);
                
                // For combination bets, check if the winning number contains the bet number
                $query->orWhere(function($q) use ($winningNumber) {
                    $q->where('is_combination', true)
                      ->whereRaw("? LIKE CONCAT('%', bet_number, '%')", [$winningNumber]);
                });
           })
           ->update(['status' => 'won']);
    }
}
