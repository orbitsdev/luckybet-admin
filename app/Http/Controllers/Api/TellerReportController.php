<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bet;
use App\Models\Draw;
use App\Models\GameType;
use App\Helpers\ApiResponse;
use App\Http\Resources\TallysheetReportResource;
use App\Http\Resources\TodaySalesResource;
use App\Http\Resources\DetailedTallysheetResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

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

            // Helper function to format numbers - only show decimal places if needed
            $formatNumber = function($value) {
                // Check if the value has decimal places
                if (floor($value) == $value) {
                    // No decimal places needed
                    return number_format($value, 0);
                } else {
                    // Has decimal places, format with 2 decimal places
                    return number_format($value, 2);
                }
            };

            return [
                'draw_id' => $draw->id,
                'type' => $draw->type ?? null,
                'winning_number' => $draw->winning_number ?? null, // If available on the Draw model
                'draw_time' => $draw->draw_time,
                'draw_time_formatted' => $formattedTime,
                'draw_label' => "Draw #{$draw->id}: {$formattedTime}",
                'gross' => $gross,
                'gross_formatted' => $formatNumber($gross),
                'sales' => $sales,
                'sales_formatted' => $formatNumber($sales),
                'hits' => $hits,
                'hits_formatted' => $formatNumber($hits),
                'kabig' => $kabig,
                'kabig_formatted' => $formatNumber($kabig),
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

        // Helper function to format numbers - only show decimal places if needed
        $formatNumber = function($value) {
            // Check if the value has decimal places
            if (floor($value) == $value) {
                // No decimal places needed
                return number_format($value, 0);
            } else {
                // Has decimal places, format with 2 decimal places
                return number_format($value, 2);
            }
        };

        $report = [
            'date' => $date,
            'date_formatted' => $formattedDate,
            'gross' => $gross,
            'gross_formatted' => $formatNumber($gross),
            'sales' => $sales,
            'sales_formatted' => $formatNumber($sales),
            'hits' => $hits,
            'hits_formatted' => $formatNumber($hits),
            'kabig' => $kabig,
            'kabig_formatted' => $formatNumber($kabig),
            'voided' => $voided,
            'voided_formatted' => $formatNumber($voided),
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

            // Helper function to format numbers - only show decimal places if needed
            $formatNumber = function($value) {
                // Check if the value has decimal places
                if (floor($value) == $value) {
                    // No decimal places needed
                    return number_format($value, 0);
                } else {
                    // Has decimal places, format with 2 decimal places
                    return number_format($value, 2);
                }
            };

            // Group draws by time
            $timeGroupedDraws = [];
            foreach ($drawSummary as $draw) {
                // Format time for better readability
                $formattedTime = Carbon::parse($draw->draw_time)->format('h:i A');
                $drawTime = $draw->draw_time;

                if (!isset($timeGroupedDraws[$drawTime])) {
                    $timeGroupedDraws[$drawTime] = [
                        'time' => $formattedTime,
                        'time_formatted' => $formattedTime,
                        'sales' => 0,
                        'hits' => 0,
                        'gross' => 0,
                        'voided' => 0,
                        'bet_count' => 0,
                    ];
                }

                // Add values to the time group
                $timeGroupedDraws[$drawTime]['sales'] += (float) $draw->sales;
                $timeGroupedDraws[$drawTime]['hits'] += (float) $draw->hits;
                $timeGroupedDraws[$drawTime]['gross'] += (float) $draw->gross;
                $timeGroupedDraws[$drawTime]['voided'] += (int) $draw->voided;
                $timeGroupedDraws[$drawTime]['bet_count'] += (int) $draw->sales > 0 ? 1 : 0;

                // Update overall totals
                $totals['sales'] += (float) $draw->sales;
                $totals['hits'] += (float) $draw->hits;
                $totals['gross'] += (float) $draw->gross;
                $totals['voided'] += (int) $draw->voided;
            }

            // Format the consolidated draw data
            $formattedDraws = [];
            foreach ($timeGroupedDraws as $time => $data) {
                $formattedDraws[] = [
                    'time' => $data['time'],
                    'time_formatted' => $data['time_formatted'],
                    'sales' => $data['sales'],
                    'sales_formatted' => $formatNumber($data['sales']),
                    'hits' => $data['hits'],
                    'hits_formatted' => $formatNumber($data['hits']),
                    'gross' => $data['gross'],
                    'gross_formatted' => $formatNumber($data['gross']),
                    'voided' => $data['voided'],
                    'voided_formatted' => $data['voided'] . ' bet(s)',
                    'bet_count' => $data['bet_count'],
                ];
            }

            // Sort by time
            usort($formattedDraws, function($a, $b) {
                return strcmp($a['time'], $b['time']);
            });

            // Add formatted values to totals using the same formatting logic
            $formatNumber = function($value) {
                // Check if the value has decimal places
                if (floor($value) == $value) {
                    // No decimal places needed
                    return number_format($value, 0);
                } else {
                    // Has decimal places, format with 2 decimal places
                    return number_format($value, 2);
                }
            };

            $formattedTotals = [
                'sales' => $totals['sales'],
                'sales_formatted' => $formatNumber($totals['sales']),
                'hits' => $totals['hits'],
                'hits_formatted' => $formatNumber($totals['hits']),
                'gross' => $totals['gross'],
                'gross_formatted' => $formatNumber($totals['gross']),
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

    /**
     * Get today's sales summary for the teller dashboard
     */
    public function todaySales(Request $request)
    {
        try {
            $user = $request->user();
            $today = now()->toDateString();

            // Get total sales for today
            $sales = Bet::where('teller_id', $user->id)
                ->whereDate('bet_date', $today)
                ->where('is_rejected', false)
                ->sum('amount');

            // Get commission rate for the teller (assuming it's stored in the users table)
            // If it's stored elsewhere, adjust this query accordingly
            $commissionRate = $user->commission_rate ?? 15; // Default to 15% if not set

            // Get cancellation count for today
            $cancellations = Bet::where('teller_id', $user->id)
                ->whereDate('bet_date', $today)
                ->where('is_rejected', true)
                ->count();

            // Helper function to format numbers with commas
            $formatNumber = function($value) {
                if (floor($value) == $value) {
                    // No decimal places needed
                    return number_format($value, 0, '.', ',');
                } else {
                    // Has decimal places, format with 2 decimal places
                    return number_format($value, 2, '.', ',');
                }
            };

            $data = [
                'sales' => $sales,
                'sales_formatted' => $formatNumber($sales),
                'commission_rate' => $commissionRate,
                'commission_rate_formatted' => $commissionRate . '%',
                'cancellations' => $cancellations,
                'cancellations_formatted' => (string) $cancellations,
            ];

            return ApiResponse::success(new TodaySalesResource($data), 'Today\'s sales summary retrieved successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve today\'s sales: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Detailed Tally Sheet report showing individual bet numbers and amounts
     * This endpoint supports pagination, date filtering, and game type filtering
     */
    public function detailedTallysheet(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'game_type_id' => 'sometimes|integer|exists:game_types,id',
                'draw_id' => 'sometimes|integer|exists:draws,id',
                'per_page' => 'sometimes|integer|min:10|max:100',
                'page' => 'sometimes|integer|min:1',
                'all' => 'sometimes|boolean',
            ]);

            $user = $request->user();
            $date = $validated['date'];
            $gameTypeId = $validated['game_type_id'] ?? null;
            $drawId = $validated['draw_id'] ?? null;
            $perPage = $validated['per_page'] ?? 50;
            $showAll = $request->boolean('all', false);

            $formattedDate = Carbon::parse($date)->format('F j, Y');

            $gameType = $gameTypeId ? GameType::find($gameTypeId) : null;

            // ✅ GROUPED query
            $query = Bet::select('bet_number', 'game_type_id', 'draw_id', DB::raw('SUM(amount) as total_amount'))
                ->where('teller_id', $user->id)
                ->whereDate('bet_date', $date)
                ->where('is_rejected', false)
                ->when($gameTypeId, fn($q) => $q->where('game_type_id', $gameTypeId))
                ->when($drawId, fn($q) => $q->where('draw_id', $drawId))
                ->groupBy('bet_number', 'game_type_id', 'draw_id')
                ->orderBy('bet_number');

            // Calculate total amount using the same query structure
            $totalAmount = Bet::where('teller_id', $user->id)
                ->whereDate('bet_date', $date)
                ->where('is_rejected', false)
                ->when($gameTypeId, fn($q) => $q->where('game_type_id', $gameTypeId))
                ->when($drawId, fn($q) => $q->where('draw_id', $drawId))
                ->sum('amount');

            $results = $showAll
                ? $query->get()
                : $query->paginate($perPage);

            $bets = $showAll ? $results : $results->items();

            $formattedBets = [];
            $betsByGameType = [];

            $gameTypes = GameType::pluck('code', 'id'); // id => code
            foreach ($gameTypes as $code) {
                $betsByGameType[$code] = [];
            }

            foreach ($bets as $bet) {
                $gameTypeCode = $gameTypes[$bet->game_type_id] ?? 'Unknown';
                $draw = Draw::find($bet->draw_id);

                $formattedBet = [
                    'bet_number' => $bet->bet_number,
                    'amount' => $bet->total_amount,
                    'amount_formatted' => floor($bet->total_amount) == $bet->total_amount
                        ? number_format($bet->total_amount, 0, '.', ',')
                        : number_format($bet->total_amount, 2, '.', ','),
                    'game_type_code' => $gameTypeCode,
                    'draw_time' => $draw?->draw_time,
                    'draw_time_formatted' => $draw?->draw_time ? Carbon::parse($draw->draw_time)->format('g:i A') : null,
                    'draw_time_simple' => $draw?->draw_time ? Carbon::parse($draw->draw_time)->format('gA') : null,
                ];

                $formattedBets[] = $formattedBet;
                $betsByGameType[$gameTypeCode][] = $formattedBet;
            }

            $responseData = [
                'date' => $date,
                'date_formatted' => $formattedDate,
                'game_type' => $gameType ? [
                    'id' => $gameType->id,
                    'code' => $gameType->code,
                    'name' => $gameType->name,
                ] : null,
                'total_amount' => $totalAmount,
                'total_amount_formatted' => floor($totalAmount) == $totalAmount
                    ? number_format($totalAmount, 0, '.', ',')
                    : number_format($totalAmount, 2, '.', ','),
                'bets' => $formattedBets,
                'bets_by_game_type' => $betsByGameType,
            ];

            if (!$showAll) {
                $responseData['pagination'] = [
                    'total' => $results->total(),
                    'current_page' => $results->currentPage(),
                ];
            }

            return ApiResponse::paginatedWithData($showAll ? new LengthAwarePaginator($bets, count($bets), $perPage) : $results,
                'Detailed tally sheet retrieved successfully',
                DetailedTallysheetResource::class,
                $responseData);

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to retrieve detailed tally sheet: ' . $e->getMessage(), 500);
        }
    }

}
