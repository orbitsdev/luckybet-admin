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
            'winning_number' => 'required|string|max:10',
        ]);

        try {
            DB::beginTransaction();
            
            // Lock the draw to prevent race conditions
            $draw = Draw::lockForUpdate()->findOrFail($data['draw_id']);
            
            // Check if the draw is still open
            if (!$draw->is_open) {
                return ApiResponse::error('This draw is already closed.', 422);
            }
            
            // Validate winning number format based on draw type
            if (!$this->validateWinningNumber($draw->type, $data['winning_number'])) {
                return ApiResponse::error('Invalid winning number format for this draw type.', 422);
            }
            
            // Prevent re-encoding of the result
            $existing = Result::where('draw_date', $draw->draw_date)
                ->where('draw_time', $draw->draw_time)
                ->where('type', $draw->type)
                ->first();
    
            if ($existing) {
                return ApiResponse::error('Result already submitted for this draw.', 409);
            }
            
            // Close the draw
            $draw->is_open = false;
            $draw->save();
            
            // Create the result
            $result = Result::create([
                'draw_date' => $draw->draw_date,
                'draw_time' => $draw->draw_time,
                'type' => $draw->type,
                'winning_number' => $data['winning_number'],
                'coordinator_id' => $request->user()->id,
            ]);
            
            // Update bet statuses based on the winning number
            $this->updateBetStatuses($draw->id, $data['winning_number']);
            
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
        
        $query = Result::with('coordinator:id,name,username')
            ->select('id', 'draw_date', 'draw_time', 'type', 'winning_number', 'coordinator_id', 'created_at')
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('draw_date', $request->date)
            )
            ->when($request->filled('type'), fn($q) =>
                $q->where('type', $request->type)
            )
            ->when($request->filled('search'), fn($q) =>
                $q->where('winning_number', 'like', '%' . $request->search . '%')
            );
        
        // Get the latest results first, then order by time within the same date
        $results = $query->orderBy('draw_date', 'desc')
                         ->orderBy('draw_time')
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
     * Update bet statuses based on the winning number
     *
     * @param int $drawId
     * @param string $winningNumber
     * @return void
     */
    private function updateBetStatuses($drawId, $winningNumber)
    {
        // Mark all active bets for this draw as lost by default
        Bet::where('draw_id', $drawId)
           ->where('status', 'active')
           ->update(['status' => 'lost']);
        
        // Mark winning bets
        Bet::where('draw_id', $drawId)
           ->where('status', 'lost')
           ->where(function($query) use ($winningNumber) {
                // Exact match
                $query->where('bet_number', $winningNumber);
                
                // For combination bets, check if the winning number contains the bet number
                // This is a simplified example - actual logic may vary based on your rules
                $query->orWhere(function($q) use ($winningNumber) {
                    $q->where('is_combination', true)
                      ->whereRaw("? LIKE CONCAT('%', bet_number, '%')", [$winningNumber]);
                });
           })
           ->update(['status' => 'won']);
    }
}
