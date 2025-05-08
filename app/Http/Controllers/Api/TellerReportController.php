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
            
            // Get the result for this draw
            $result = $draw->result;
            
            // Calculate hits based on matching bet numbers with winning numbers by game type
            $hits = 0;
            if ($result) {
                $hits = $drawBets->filter(function($bet) use ($result) {
                    // Get the winning number based on game type
                    $gameTypeCode = $bet->gameType->code ?? '';
                    $winningNumber = null;
                    
                    if ($gameTypeCode === 'S2' && !empty($result->s2_winning_number)) {
                        $winningNumber = $result->s2_winning_number;
                    } elseif ($gameTypeCode === 'S3' && !empty($result->s3_winning_number)) {
                        $winningNumber = $result->s3_winning_number;
                    } elseif ($gameTypeCode === 'D4' && !empty($result->d4_winning_number)) {
                        $winningNumber = $result->d4_winning_number;
                    }
                    
                    // Check if bet number matches winning number
                    return $winningNumber && $bet->bet_number == $winningNumber;
                })->sum('amount');
            }
            $kabig = $gross - $hits;

            // Format draw time for better readability
            $formattedTime = Carbon::parse($draw->draw_time)->format('h:i A');

            return [
                'draw_id' => $draw->id,
                'type' => $draw->type ?? null,
                'winning_number' => $draw->winning_number ?? null, // If available on the Draw model
                'draw_time' => $draw->draw_time,
                'draw_time_formatted' => $formattedTime,
                'draw_label' => "Draw #{$draw->id}: {$formattedTime}",
                'gross' => $gross,
                'gross_formatted' => '₱' . number_format($gross, 2),
                'sales' => $sales,
                'sales_formatted' => '₱' . number_format($sales, 2),
                'hits' => $hits,
                'hits_formatted' => '₱' . number_format($hits, 2),
                'kabig' => $kabig,
                'kabig_formatted' => '₱' . number_format($kabig, 2),
            ];
        });

        // Overall totals
        $gross = $bets->sum('amount');
        $sales = $bets->where('is_rejected', false)->sum('amount');
        // Calculate total hits based on matching bet numbers with winning numbers by game type
        $hits = 0;
        foreach ($draws as $draw) {
            $result = $draw->result;
            if ($result) {
                $hits += $bets->where('draw_id', $draw->id)
                    ->filter(function($bet) use ($result) {
                        // Get the winning number based on game type
                        $gameTypeCode = $bet->gameType->code ?? '';
                        $winningNumber = null;
                        
                        if ($gameTypeCode === 'S2' && !empty($result->s2_winning_number)) {
                            $winningNumber = $result->s2_winning_number;
                        } elseif ($gameTypeCode === 'S3' && !empty($result->s3_winning_number)) {
                            $winningNumber = $result->s3_winning_number;
                        } elseif ($gameTypeCode === 'D4' && !empty($result->d4_winning_number)) {
                            $winningNumber = $result->d4_winning_number;
                        }
                        
                        // Check if bet number matches winning number
                        return $winningNumber && $bet->bet_number == $winningNumber;
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
            'gross_formatted' => '₱' . number_format($gross, 2),
            'sales' => $sales,
            'sales_formatted' => '₱' . number_format($sales, 2),
            'hits' => $hits,
            'hits_formatted' => '₱' . number_format($hits, 2),
            'kabig' => $kabig,
            'kabig_formatted' => '₱' . number_format($kabig, 2),
            'voided' => $voided,
            'voided_formatted' => '₱' . number_format($voided, 2),
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
                    gt.code as game_type_code,
                    gt.name as game_type_name,
                    CASE 
                        WHEN gt.code = 'S2' THEN r.s2_winning_number
                        WHEN gt.code = 'S3' THEN r.s3_winning_number
                        WHEN gt.code = 'D4' THEN r.d4_winning_number
                        ELSE NULL
                    END as winning_number,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) as sales,
                    SUM(CASE 
                        WHEN gt.code = 'S2' AND b.bet_number = r.s2_winning_number THEN b.amount
                        WHEN gt.code = 'S3' AND b.bet_number = r.s3_winning_number THEN b.amount
                        WHEN gt.code = 'D4' AND b.bet_number = r.d4_winning_number THEN b.amount
                        ELSE 0 
                    END) as hits,
                    COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END) -
                    SUM(CASE 
                        WHEN gt.code = 'S2' AND b.bet_number = r.s2_winning_number THEN b.amount
                        WHEN gt.code = 'S3' AND b.bet_number = r.s3_winning_number THEN b.amount
                        WHEN gt.code = 'D4' AND b.bet_number = r.d4_winning_number THEN b.amount
                        ELSE 0 
                    END) as gross
                FROM bets b
                JOIN draws d ON b.draw_id = d.id
                JOIN game_types gt ON b.game_type_id = gt.id
                LEFT JOIN results r ON r.draw_date = d.draw_date AND r.draw_time = d.draw_time
                WHERE b.teller_id = ?
                AND DATE(b.bet_date) = ?
                $drawIdFilter
                GROUP BY d.id, d.draw_time, gt.code, gt.name, r.s2_winning_number, r.s3_winning_number, r.d4_winning_number
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
                    'time_formatted' => $formattedTime,
                    'game_type_code' => $draw->game_type_code,
                    'game_type_name' => $draw->game_type_name,
                    'winning_number' => $draw->winning_number ?? '--',
                    'sales' => (float) $draw->sales,
                    'sales_formatted' => '₱' . number_format((float) $draw->sales, 2),
                    'hits' => (float) $draw->hits,
                    'hits_formatted' => '₱' . number_format((float) $draw->hits, 2),
                    'gross' => (float) $draw->gross,
                    'gross_formatted' => '₱' . number_format((float) $draw->gross, 2),
                    'voided' => (int) $draw->voided,
                    'voided_formatted' => (int) $draw->voided . ' bet(s)',
                    'draw_label' => "Draw #{$draw->draw_id}: {$formattedTime} ({$draw->game_type_name})",
                ];

                $formattedDraws[] = $formattedDraw;

                // Update totals
                $totals['sales'] += (float) $draw->sales;
                $totals['hits'] += (float) $draw->hits;
                $totals['gross'] += (float) $draw->gross;
                $totals['voided'] += (int) $draw->voided;
            }

            // Add formatted values to totals
            $formattedTotals = [
                'sales' => $totals['sales'],
                'sales_formatted' => '₱' . number_format($totals['sales'], 2),
                'hits' => $totals['hits'],
                'hits_formatted' => '₱' . number_format($totals['hits'], 2),
                'gross' => $totals['gross'],
                'gross_formatted' => '₱' . number_format($totals['gross'], 2),
                'voided' => $totals['voided'],
                'voided_formatted' => $totals['voided'] . ' bet(s)',
            ];
            
            return ApiResponse::success([
                'date' => $date,
                'date_formatted' => $formattedDate,
                'totals' => $formattedTotals,
                'draws' => $formattedDraws,
            ], 'Tally sheet generated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to generate tally sheet: ' . $e->getMessage(), 500);
        }
    }
}
