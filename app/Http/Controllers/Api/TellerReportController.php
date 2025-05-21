<?php

namespace App\Http\Controllers\Api;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\GameType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TodaySalesResource;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\CommissionReportResource;
use App\Http\Resources\TallysheetReportResource;
use App\Http\Resources\DetailedTallysheetResource;

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

        // Build SQL and params
        $where = "WHERE DATE(b.bet_date) = ?";
        $params = [$date];
        if ($tellerId) {
            $where .= " AND b.teller_id = ?";
            $params[] = $tellerId;
        }
        if ($locationId) {
            $where .= " AND b.location_id = ?";
            $params[] = $locationId;
        }
        if ($drawId) {
            $where .= " AND d.id = ?";
            $params[] = $drawId;
        }

        $sql = "
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
                    WHEN gt.code = 'S2' AND b.bet_number = r.s2_winning_number THEN COALESCE(b.winning_amount, 0)
                    WHEN gt.code = 'S3' AND b.bet_number = r.s3_winning_number THEN COALESCE(b.winning_amount, 0)
                    -- D4 pure
                    WHEN gt.code = 'D4' AND (b.d4_sub_selection IS NULL OR b.d4_sub_selection = '') AND b.bet_number = r.d4_winning_number THEN COALESCE(b.winning_amount, 0)
                    -- D4-S2: last 2 digits
                    WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S2' AND RIGHT(r.d4_winning_number, 2) = LPAD(b.bet_number, 2, '0') THEN COALESCE(b.winning_amount, 0)
                    -- D4-S3: last 3 digits
                    WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S3' AND RIGHT(r.d4_winning_number, 3) = LPAD(b.bet_number, 3, '0') THEN COALESCE(b.winning_amount, 0)
                    ELSE 0
                END) as gross,
                COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided
            FROM bets b
            JOIN draws d ON b.draw_id = d.id
            JOIN game_types gt ON b.game_type_id = gt.id
            LEFT JOIN results r ON r.draw_date = d.draw_date AND r.draw_time = d.draw_time
            $where
            GROUP BY d.id, d.draw_time, gt.code, gt.name, r.s2_winning_number, r.s3_winning_number, r.d4_winning_number
            ORDER BY d.draw_time ASC
        ";

        $drawSummary = DB::select($sql, $params);

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
                        WHEN gt.code = 'S2' AND b.bet_number = r.s2_winning_number THEN COALESCE(b.winning_amount, 0)
                        WHEN gt.code = 'S3' AND b.bet_number = r.s3_winning_number THEN COALESCE(b.winning_amount, 0)
                        -- D4 pure
                        WHEN gt.code = 'D4' AND (b.d4_sub_selection IS NULL OR b.d4_sub_selection = '') AND b.bet_number = r.d4_winning_number THEN COALESCE(b.winning_amount, 0)
                        -- D4-S2: last 2 digits
                        WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S2' AND RIGHT(r.d4_winning_number, 2) = LPAD(b.bet_number, 2, '0') THEN COALESCE(b.winning_amount, 0)
                        -- D4-S3: last 3 digits
                        WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S3' AND RIGHT(r.d4_winning_number, 3) = LPAD(b.bet_number, 3, '0') THEN COALESCE(b.winning_amount, 0)
                        ELSE 0
                    END) as hits,
                    COUNT(CASE WHEN b.is_rejected = 1 THEN 1 END) as voided,
                    SUM(CASE WHEN b.is_rejected = 0 THEN b.amount ELSE 0 END)
                    -
                    SUM(CASE
                        WHEN gt.code = 'S2' AND b.bet_number = r.s2_winning_number THEN COALESCE(b.winning_amount, 0)
                        WHEN gt.code = 'S3' AND b.bet_number = r.s3_winning_number THEN COALESCE(b.winning_amount, 0)
                        -- D4 pure
                        WHEN gt.code = 'D4' AND (b.d4_sub_selection IS NULL OR b.d4_sub_selection = '') AND b.bet_number = r.d4_winning_number THEN COALESCE(b.winning_amount, 0)
                        -- D4-S2: last 2 digits
                        WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S2' AND RIGHT(r.d4_winning_number, 2) = LPAD(b.bet_number, 2, '0') THEN COALESCE(b.winning_amount, 0)
                        -- D4-S3: last 3 digits
                        WHEN gt.code = 'D4' AND b.d4_sub_selection = 'S3' AND RIGHT(r.d4_winning_number, 3) = LPAD(b.bet_number, 3, '0') THEN COALESCE(b.winning_amount, 0)
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
            $commissionRate = $user->commission_rate ?? 10; // Default to 10% if not set

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
                'date' => 'sometimes|date',
                'game_type_id' => 'sometimes|integer|exists:game_types,id',
                'draw_id' => 'sometimes|integer|exists:draws,id',
                'per_page' => 'sometimes|integer|min:10|max:100',
                'page' => 'sometimes|integer|min:1',
                'all' => 'sometimes|boolean',
            ]);

            $user = $request->user();
            $date = $validated['date'] ?? Carbon::today()->format('Y-m-d');
            $gameTypeId = $validated['game_type_id'] ?? null;
            $drawId = $validated['draw_id'] ?? null;
            $perPage = $validated['per_page'] ?? 50;
            $showAll = $request->boolean('all', false);

            $formattedDate = Carbon::parse($date)->format('F j, Y');

            $gameType = $gameTypeId ? GameType::find($gameTypeId) : null;

            // ✅ GROUPED query
            $query = Bet::select('bet_number', 'game_type_id', 'draw_id', 'd4_sub_selection', DB::raw('SUM(amount) as total_amount'))
                ->where('teller_id', $user->id)
                ->whereDate('bet_date', $date)
                ->where('is_rejected', false)
                ->when($gameTypeId, fn($q) => $q->where('game_type_id', $gameTypeId))
                ->when($drawId, fn($q) => $q->where('draw_id', $drawId))
                ->groupBy('bet_number', 'game_type_id', 'draw_id', 'd4_sub_selection')
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

            // Get valid game type codes from the database
            $gameTypes = GameType::pluck('code', 'id'); // id => code

            // Initialize categories for all valid game types
            foreach ($gameTypes as $code) {
                $betsByGameType[$code] = [];
            }

            // Add special categories for D4-S2 and D4-S3
            $betsByGameType['D4-S2'] = [];
            $betsByGameType['D4-S3'] = [];

            // Do NOT remove S2 or S3 categories; keep them in the response so all categories are always present
            // (unset lines removed as requested)

            foreach ($bets as $bet) {
                $gameTypeCode = $gameTypes[$bet->game_type_id] ?? 'Unknown';
                $draw = Draw::find($bet->draw_id);

                // Ensure d4_sub_selection is only used for D4 game type
                $d4SubSelection = ($gameTypeCode === 'D4') ? $bet->d4_sub_selection : null;

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
                    'd4_sub_selection' => $d4SubSelection,
                    'display_type' => $gameTypeCode === 'D4' && $d4SubSelection
                        ? $gameTypeCode . '-' . $d4SubSelection
                        : $gameTypeCode
                ];

                $formattedBets[] = $formattedBet;

                // Handle D4 game type specially to avoid duplication
                if ($gameTypeCode === 'D4') {
                    if ($d4SubSelection) {
                        // If it has a sub-selection, add only to the specific D4-S2 or D4-S3 category
                        $betsByGameType["D4-{$d4SubSelection}"][] = $formattedBet;
                    } else {
                        // If it doesn't have a sub-selection, add to the regular D4 category
                        $betsByGameType[$gameTypeCode][] = $formattedBet;
                    }
                } else if (isset($betsByGameType[$gameTypeCode])) {
                    // For all other game types, add to their regular category
                    $betsByGameType[$gameTypeCode][] = $formattedBet;
                }
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

    /**
     * Commission report for teller (by date, default today)
     */
    public function commissionReport(Request $request)
    {
        $validated = $request->validate([
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        $user = $request->user();
        $date = $validated['date'] ?? now()->toDateString();
        $formattedDate = \Carbon\Carbon::parse($date)->format('F j, Y');

        // Get commission rate (default 10%)
        $commissionRate = $user->commission_rate ?? 10;

        // Sum total sales for this teller and date (not rejected)
        $totalSales = Bet::where('teller_id', $user->id)
            ->whereDate('bet_date', $date)
            ->where('is_rejected', false)
            ->sum('amount');

        // Calculate commission
        $commissionAmount = $totalSales * ($commissionRate / 100);

        $data = [
            'date' => $date,
            'date_formatted' => $formattedDate,
            'commission_rate' => $commissionRate,
            'total_sales' => $totalSales,
            'commission_amount' => $commissionAmount,
        ];

        return ApiResponse::success(
            new CommissionReportResource((object) $data),
            'Commission report generated successfully'
        );
    }

}
