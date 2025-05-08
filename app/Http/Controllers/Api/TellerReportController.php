<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\Draw;
use App\Helpers\ApiResponse;
use App\Http\Resources\TallysheetReportResource;
// use App\Http\Resources\SalesReportResource; // Uncomment/create if you want a custom resource for sales
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TellerReportController extends Controller
{
    /**
     * Tallysheet report (per-draw breakdown)
     */
    public function tallysheet(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'teller_id' => 'sometimes|integer|exists:users,id',
            'location_id' => 'sometimes|integer|exists:locations,id',
            'draw_id' => 'sometimes|integer|exists:draws,id',
        ]);

        $date = $validated['date'];
        $tellerId = $validated['teller_id'] ?? null;
        $locationId = $validated['location_id'] ?? null;
        $drawId = $validated['draw_id'] ?? null;

        // Query all draws for the date (optionally filter by draw_id)
        $drawsQuery = Draw::where('draw_date', $date);
        if ($drawId) $drawsQuery->where('id', $drawId);
        $draws = $drawsQuery->get();

        // Query all bets for the date (optionally filter by teller/location)
        $betsQuery = Bet::whereDate('bet_date', $date);
        if ($tellerId) $betsQuery->where('teller_id', $tellerId);
        if ($locationId) $betsQuery->where('location_id', $locationId);
        $bets = $betsQuery->get();

        // Aggregate per-draw
        $perDraw = $draws->map(function ($draw) use ($bets) {
            $drawBets = $bets->where('draw_id', $draw->id);
            $gross = $drawBets->sum('amount');
            $sales = $drawBets->where('is_rejected', false)->sum('amount');
            // Calculate hits based on matching bet numbers with winning number
            $hits = 0;
            if ($draw->winning_number) {
                $hits = $drawBets->filter(function($bet) use ($draw) {
                    return $bet->bet_number == $draw->winning_number;
                })->sum('amount');
            }
            $kabig = $gross - $hits;
            return [
                'draw_id' => $draw->id,
                'type' => $draw->type ?? null,
                'winning_number' => $draw->winning_number ?? null, // If available on the Draw model
                'draw_label' => $draw->id . ': ' . $draw->draw_time,
                'gross' => $gross,
                'sales' => $sales,
                'hits' => $hits,
                'kabig' => $kabig,
            ];
        });

        // Overall totals
        $gross = $bets->sum('amount');
        $sales = $bets->where('is_rejected', false)->sum('amount');
        // Calculate total hits based on matching bet numbers with winning numbers
        $hits = 0;
        foreach ($draws as $draw) {
            if ($draw->winning_number) {
                $hits += $bets->where('draw_id', $draw->id)
                    ->filter(function($bet) use ($draw) {
                        return $bet->bet_number == $draw->winning_number;
                    })->sum('amount');
            }
        }
        $kabig = $gross - $hits;
        $voided = $bets->where('is_rejected', true)->sum('amount');

        $formattedDate = Carbon::parse($date)->format('F j, Y');

        $report = [
            'date' => $date,
            'date_formatted' => $formattedDate,
            'gross' => $gross,
            'sales' => $sales,
            'hits' => $hits,
            'kabig' => $kabig,
            'voided' => $voided,
            'per_draw' => $perDraw,
        ];

        return ApiResponse::success(new TallysheetReportResource($report), 'Tallysheet report generated');
    }

    /**
     * Sales report (per-draw sales summary)
     */
    public function sales(Request $request)
    {
        try {
           
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
            $drawIdFilter = '';
            $params = [$user->id, $date];
            if ($request->filled('draw_id')) {
                $drawIdFilter = ' AND d.id = ?';
                $params[] = $request->draw_id;
            }
            $drawSummary = DB::select("
                SELECT 
                    d.id as draw_id,
                    d.draw_time as draw_time,
                    d.type as type,
                    r.winning_number as winning_number,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) as sales,
                    SUM(CASE WHEN b.bet_number = r.winning_number THEN b.amount ELSE 0 END) as hits,
                    COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) - 
                    SUM(CASE WHEN b.bet_number = r.winning_number THEN b.amount ELSE 0 END) as gross
                FROM bets b
                JOIN draws d ON b.draw_id = d.id
                LEFT JOIN results r ON r.draw_date = d.draw_date AND r.draw_time = d.draw_time AND r.type = d.type
                WHERE b.teller_id = ?
                AND DATE(b.bet_date) = ?
                $drawIdFilter
                GROUP BY d.id, d.draw_time, d.type, r.winning_number
                ORDER BY d.draw_time ASC
            ", $params);
            
            if (empty($drawSummary)) {
                return ApiResponse::success([
                    'date' => $date,
                    'date_formatted' => $formattedDate,
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
                'date' => $date,
                'date_formatted' => $formattedDate,
                'totals' => $totals,
                'draws' => $formattedDraws,
            ], 'Tally sheet generated successfully');
            
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to generate tally sheet: ' . $e->getMessage(), 500);
        }
    }
}
