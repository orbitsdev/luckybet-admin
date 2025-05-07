<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Draw;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TallySheetController extends Controller
{
    /**
     * Generate a tally sheet for a teller for a specific date
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate date parameter if provided
            if ($request->filled('date')) {
                $request->validate([
                    'date' => 'date_format:Y-m-d'
                ]);
            }
            
            $user = $request->user();
            $date = $request->date ?? now()->toDateString();
            
            // Format date for display
            $formattedDate = Carbon::parse($date)->format('F j, Y');
            
            // Use raw SQL query for better performance
            $drawSummary = DB::select("
                SELECT 
                    d.id as draw_id,
                    d.draw_time as draw_time,
                    d.type as type,
                    r.winning_number as winning_number,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) as sales,
                    SUM(CASE WHEN b.is_claimed = 1 THEN b.amount ELSE 0 END) as hits,
                    COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) - 
                    SUM(CASE WHEN b.is_claimed = 1 THEN b.amount ELSE 0 END) as gross
                FROM bets b
                JOIN draws d ON b.draw_id = d.id
                LEFT JOIN results r ON r.draw_date = d.draw_date AND r.draw_time = d.draw_time AND r.type = d.type
                WHERE b.teller_id = ?
                AND DATE(b.bet_date) = ?
                GROUP BY d.id, d.draw_time, d.type, r.winning_number
                ORDER BY d.draw_time ASC
            ", [$user->id, $date]);
            
            if (empty($drawSummary)) {
                return ApiResponse::success([
                    'date' => $formattedDate,
                    'totals' => [
                        'sales' => 0,
                        'hits' => 0,
                        'gross' => 0,
                        'voided' => 0,
                    ],
                    'draws' => [],
                ], 'No bets found for this date');
            }
            
            // Calculate totals
            $totals = [
                'sales' => 0,
                'hits' => 0,
                'gross' => 0,
                'voided' => 0,
            ];
            
            // Format the draw summary data
            $formattedDraws = [];
            foreach ($drawSummary as $draw) {
                // Format time for better readability
                $formattedTime = Carbon::parse($draw->draw_time)->format('h:i A');
                
                $formattedDraw = [
                    'draw_id' => $draw->draw_id,
                    'time' => $formattedTime,
                    'type' => $draw->type,
                    'winning_number' => $draw->winning_number ?? '--',
                    'sales' => (float) $draw->sales,
                    'hits' => (float) $draw->hits,
                    'gross' => (float) $draw->gross,
                    'voided' => (int) $draw->voided,
                ];
                
                $formattedDraws[] = $formattedDraw;
                
                // Update totals
                $totals['sales'] += (float) $draw->sales;
                $totals['hits'] += (float) $draw->hits;
                $totals['gross'] += (float) $draw->gross;
                $totals['voided'] += (int) $draw->voided;
            }
    
            return ApiResponse::success([
                'date' => $formattedDate,
                'totals' => $totals,
                'draws' => $formattedDraws,
            ], 'Tally sheet generated successfully');
            
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to generate tally sheet: ' . $e->getMessage(), 500);
        }
    }
}
