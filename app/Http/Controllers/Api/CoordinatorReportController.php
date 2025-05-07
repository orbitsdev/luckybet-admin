<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CoordinatorReportController extends Controller
{
    /**
     * Get a summary report for a coordinator showing teller performance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Validate date if provided
            if ($request->filled('date')) {
                $request->validate([
                    'date' => 'date_format:Y-m-d'
                ]);
            }
            
            $user = $request->user();
            $date = $request->date ?? now()->toDateString();
            
            // Get teller summary using raw SQL for better performance
            $tellerSummary = DB::select("
                SELECT 
                    b.teller_id,
                    u.name,
                    u.username,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) as sales,
                    SUM(CASE WHEN b.is_claimed = 1 THEN b.amount ELSE 0 END) as hits,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) - 
                    SUM(CASE WHEN b.is_claimed = 1 THEN b.amount ELSE 0 END) as gross,
                    COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided,
                    COUNT(DISTINCT b.id) as total_bets
                FROM bets b
                JOIN users u ON b.teller_id = u.id
                WHERE DATE(b.bet_date) = ?
                AND b.location_id = ?
                GROUP BY b.teller_id, u.name, u.username
                ORDER BY u.name ASC
            ", [$date, $user->location_id]);
            
            // Calculate totals
            $totals = [
                'sales' => 0,
                'hits' => 0,
                'gross' => 0,
                'voided' => 0,
                'total_bets' => 0,
            ];
            
            // Format teller data and calculate totals
            $tellers = [];
            foreach ($tellerSummary as $teller) {
                // Convert to proper types
                $teller->sales = (float) $teller->sales;
                $teller->hits = (float) $teller->hits;
                $teller->gross = (float) $teller->gross;
                $teller->voided = (int) $teller->voided;
                $teller->total_bets = (int) $teller->total_bets;
                
                // Add to totals
                $totals['sales'] += $teller->sales;
                $totals['hits'] += $teller->hits;
                $totals['gross'] += $teller->gross;
                $totals['voided'] += $teller->voided;
                $totals['total_bets'] += $teller->total_bets;
                
                // Get the user to access the profile_photo_url attribute directly
                $tellerUser = User::find($teller->teller_id);
                
                // Add to tellers array
                $tellers[] = [
                    'teller_id' => $teller->teller_id,
                    'name' => $teller->name,
                    'username' => $teller->username,
                    'sales' => $teller->sales,
                    'hits' => $teller->hits,
                    'gross' => $teller->gross,
                    'voided' => $teller->voided,
                    'total_bets' => $teller->total_bets,
                    // Use the profile_photo_url attribute directly from the User model
                    'profile_photo_url' => $tellerUser ? $tellerUser->profile_photo_url : null
                ];
            }
            
            // Get draw type breakdown
            $drawTypeSummary = DB::select("
                SELECT 
                    d.type,
                    COUNT(b.id) as bet_count,
                    SUM(b.amount) as total_amount
                FROM bets b
                JOIN draws d ON b.draw_id = d.id
                WHERE DATE(b.bet_date) = ?
                AND b.location_id = ?
                AND b.status IN ('active', 'won', 'lost')
                GROUP BY d.type
                ORDER BY total_amount DESC
            ", [$date, $user->location_id]);
            
            return ApiResponse::success([
                'date' => $date,
                'totals' => $totals,
                'tellers' => $tellers,
                'draw_types' => $drawTypeSummary
            ], 'Coordinator summary loaded');
            
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to load coordinator summary: ' . $e->getMessage(), 500);
        }
    }
}
