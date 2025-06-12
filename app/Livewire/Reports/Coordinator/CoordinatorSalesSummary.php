<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CoordinatorSalesSummary extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use WithPagination;
    use InteractsWithActions;

    public $date;
    public $searchTerm = '';
    public $salesData = [];
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $debugInfo = [];
    public $hasPendingDraws = false;
public $missingResults = [];
public array $validDrawIds = [];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadSalesData();
    }

    public function hydrate()
    {
        // Convert date string to Carbon object for queries if needed
        if (is_string($this->date)) {
            $this->dateObj = Carbon::parse($this->date);
        }
    }

    public function loadSalesData()
    {
        $user = Auth::user();
        $this->debugInfo['current_user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role
        ];

        // Get all draws for the selected date
        $formattedDate = is_string($this->date) ? $this->date : Carbon::parse($this->date)->format('Y-m-d');
        $this->debugInfo['formatted_date'] = $formattedDate;

  $draws = Draw::with('result')
    ->whereDate('draw_date', $formattedDate)
    ->orderBy('draw_time')
    ->get();

// Check completeness
$this->missingResults = [];
$this->hasPendingDraws = false;

$validDraws = [];

foreach ($draws as $draw) {
    $missing = [];

    if (!$draw->result) {
        $missing = ['S2', 'S3', 'D4'];
    } else {
        if (!$draw->result->s2_winning_number) $missing[] = 'S2';
        if (!$draw->result->s3_winning_number) $missing[] = 'S3';
        if (!$draw->result->d4_winning_number) $missing[] = 'D4';
    }

    if (!empty($missing)) {
        $this->hasPendingDraws = true;
        $this->missingResults[] = [
            'time' => Carbon::parse($draw->draw_time)->format('g:i A'),
            'missing' => $missing,
        ];
    } else {
        $validDraws[] = $draw;
    }
}

// Only use valid draws for calculations
$drawIds = collect($validDraws)->pluck('id')->toArray();
$this->validDrawIds = $drawIds;


$this->hasPendingDraws = count($this->missingResults) > 0;

        $this->debugInfo['draws_count'] = count($drawIds);

        if (empty($drawIds)) {
            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;
            $this->debugInfo['error'] = 'No draws found for this date';
            return;
        }

        // For admin view, we want to show data grouped by coordinators
        if ($user->role === 'admin') {
            // Get all coordinators
            $coordinators = User::where('role', 'coordinator')->get();
            $this->debugInfo['coordinator_count'] = $coordinators->count();

            if ($coordinators->isEmpty()) {
                $this->salesData = [];
                $this->totalSales = 0;
                $this->totalHits = 0;
                $this->totalGross = 0;
                $this->debugInfo['error'] = 'No coordinators found';
                return;
            }

            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;

            // Process each coordinator
            foreach ($coordinators as $coordinator) {
                // Get tellers for this coordinator
                $tellerIds = User::where('coordinator_id', $coordinator->id)
                    ->where('role', 'teller')
                    ->pluck('id')
                    ->toArray();

                if (empty($tellerIds)) {
                    continue; // Skip coordinators with no tellers
                }

                // Get bets for these tellers - only include bets with receipts in 'placed' status
                $betsQuery = Bet::placed()->whereIn('teller_id', $tellerIds)
                    ->whereIn('draw_id', $drawIds)
                    ->where('is_rejected', false);

                // Apply search filter if provided
                if (!empty($this->searchTerm)) {
                    // Search by coordinator name
                    if (stripos($coordinator->name, $this->searchTerm) === false) {
                        continue; // Skip this coordinator if name doesn't match search
                    }
                }

                // Calculate totals using the same method as CoordinatorTellerSalesSummary
                $totalSales = $betsQuery->sum('amount');
                $totalHits = $betsQuery->whereNotNull('winning_amount')->where('winning_amount', '>', 0)->sum('winning_amount');
                $totalGross = $totalSales - $totalHits;

                if ($totalSales > 0) { // Only add coordinators with sales
                    $this->salesData[] = [
                        'id' => $coordinator->id,
                        'name' => $coordinator->name,
                        'total_sales' => $totalSales,
                        'total_hits' => $totalHits,
                        'total_gross' => $totalGross,
                    ];

                    $this->totalSales += $totalSales;
                    $this->totalHits += $totalHits;
                    $this->totalGross += $totalGross;
                }
            }

            return;
        } else if ($user->role === 'coordinator') {
            // For coordinators, get only their tellers
            $tellerIds = User::where('coordinator_id', $user->id)
                ->where('role', 'teller')
                ->pluck('id')
                ->toArray();
            $this->debugInfo['coordinator_id'] = $user->id;
        } else {
            // For other users (like testing), get all tellers
            $tellerIds = User::where('role', 'teller')
                ->pluck('id')
                ->toArray();
            $this->debugInfo['default_view'] = true;
        }

        $this->debugInfo['teller_count'] = count($tellerIds);

        if (empty($tellerIds)) {
            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;
            $this->debugInfo['error'] = 'No tellers found';
            return;
        }

        // We already have the draws from above, no need to query again

        // Check if there are any bets for these draws and tellers - only include bets with receipts in 'placed' status
        $betCount = Bet::placed()->whereIn('draw_id', $drawIds)
            ->whereIn('teller_id', $tellerIds)
            ->count();

        $this->debugInfo['bet_count'] = $betCount;

        // Get all bets for these tellers on this date - only include bets with receipts in 'placed' status
        $betsQuery = Bet::placed()->whereIn('teller_id', $tellerIds)
            ->whereIn('draw_id', $drawIds)
            ->where('is_rejected', false);

        $this->debugInfo['query'] = [
            'teller_ids' => $tellerIds,
            'draw_ids' => $drawIds,
        ];

        $betsQuery = $betsQuery->with(['teller', 'draw', 'gameType']);

        // Apply search filter if provided
        if (!empty($this->searchTerm)) {
            $betsQuery->whereHas('teller', function($query) {
                $query->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $bets = $betsQuery->get();

        // Group data by teller and draw
        $salesByTellerAndDraw = [];
        $tellerTotals = [];

        foreach ($bets as $bet) {
            $tellerId = $bet->teller_id;
            $tellerName = $bet->teller->name;
            $drawId = $bet->draw_id;
            $drawTime = $bet->draw->draw_time;
            $gameTypeCode = $bet->gameType->code;

            // Initialize teller data if not exists
            if (!isset($salesByTellerAndDraw[$tellerId])) {
                $salesByTellerAndDraw[$tellerId] = [
                    'name' => $tellerName,
                    'draws' => [],
                    'total_sales' => 0,
                    'total_hits' => 0,
                    'total_gross' => 0,
                ];
            }

            // Initialize draw data if not exists
            if (!isset($salesByTellerAndDraw[$tellerId]['draws'][$drawId])) {
                $salesByTellerAndDraw[$tellerId]['draws'][$drawId] = [
                    'draw_time' => $drawTime,
                    'draw_time_formatted' => Carbon::parse($drawTime)->format('g:i A'),
                    'sales' => 0,
                    'hits' => 0,
                    'gross' => 0,
                ];
            }

            // Add bet amount to sales
            $salesByTellerAndDraw[$tellerId]['draws'][$drawId]['sales'] += $bet->amount;
            $salesByTellerAndDraw[$tellerId]['total_sales'] += $bet->amount;

            // Calculate hits and gross
            $result = $bet->draw->result;
            if ($result) {
                $isWinner = false;

                // Check if bet is a winner based on game type
                switch ($gameTypeCode) {
                    case 'S2':
                        $isWinner = $bet->bet_number === $result->s2_winning_number;
                        break;

                    case 'S3':
                        $isWinner = $bet->bet_number === $result->s3_winning_number;
                        break;

                    case 'D4':
                        $isWinner = $bet->bet_number === $result->d4_winning_number;

                        // D4 sub-selection logic
                        if (!$isWinner && $bet->d4_sub_selection && $result->d4_winning_number) {
                            $sub = strtoupper($bet->d4_sub_selection);
                            if ($sub === 'S2') {
                                // Compare last 2 digits of D4 result to bet number
                                $isWinner = substr($result->d4_winning_number, -2) === str_pad($bet->bet_number, 2, '0', STR_PAD_LEFT);
                            } else if ($sub === 'S3') {
                                // Compare last 3 digits of D4 result to bet number
                                $isWinner = substr($result->d4_winning_number, -3) === str_pad($bet->bet_number, 3, '0', STR_PAD_LEFT);
                            }
                        }
                        break;
                }

                if ($isWinner) {
                    $winningAmount = $bet->winning_amount;
                    $salesByTellerAndDraw[$tellerId]['draws'][$drawId]['hits'] += $winningAmount;
                    $salesByTellerAndDraw[$tellerId]['total_hits'] += $winningAmount;
                }
            }
        }

        // Calculate gross (sales - hits)
        foreach ($salesByTellerAndDraw as $tellerId => &$tellerData) {
            foreach ($tellerData['draws'] as $drawId => &$drawData) {
                $drawData['gross'] = $drawData['sales'] - $drawData['hits'];
            }
            $tellerData['total_gross'] = $tellerData['total_sales'] - $tellerData['total_hits'];
        }

        // Calculate totals
        $this->totalSales = 0;
        $this->totalHits = 0;
        $this->totalGross = 0;

        foreach ($salesByTellerAndDraw as $tellerData) {
            $this->totalSales += $tellerData['total_sales'];
            $this->totalHits += $tellerData['total_hits'];
            $this->totalGross += $tellerData['total_gross'];
        }

        $this->salesData = $salesByTellerAndDraw;
    }

    public function updatedDate()
    {
        $this->loadSalesData();
    }

    public function updatedSearchTerm()
    {
        $this->loadSalesData();
    }

    public function resetSearch()
    {
        $this->searchTerm = '';
        $this->loadSalesData();
    }

    public function resetFilters()
    {
        $this->searchTerm = '';
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadSalesData();
    }

    public function viewCoordinatorSummaryAction(): Action
    {
        return Action::make('viewCoordinatorSummary')
            ->label('QUICK VIEW')
            ->icon('heroicon-o-eye')
            ->color('cool-gray')
            ->size('xs')
            ->modalHeading(fn (array $arguments) => 'Sales Summary for ' . $this->getCoordinatorName($arguments['coordinator_id']))
            ->modalWidth('6xl')
            ->modalContent(function (array $arguments) {
                $coordinatorId = $arguments['coordinator_id'];
                $coordinatorData = null;

                // Find the coordinator data in the salesData array
                foreach ($this->salesData as $item) {
                    if ($item['id'] == $coordinatorId) {
                        $coordinatorData = $item;
                        break;
                    }
                }

                // Get teller count for this coordinator
                $tellerCount = User::where('coordinator_id', $coordinatorId)
                    ->where('role', 'teller')
                    ->count();

                // Get active teller count (tellers with sales on this date)
                $formattedDate = is_string($this->date) ? $this->date : Carbon::parse($this->date)->format('Y-m-d');
           $draws = $this->validDrawIds;

                $tellers = User::where('coordinator_id', $coordinatorId)
                    ->where('role', 'teller')
                    ->get();

                $tellerIds = $tellers->pluck('id')->toArray();

                $activeTellerCount = Bet::placed()->whereIn('teller_id', $tellerIds)
                    ->whereIn('draw_id', $draws)
                    ->where('is_rejected', false)
                    ->select('teller_id')
                    ->distinct()
                    ->count();

                // Get bet count - only include bets with receipts in 'placed' status
                $betCount = Bet::placed()->whereIn('teller_id', $tellerIds)
                    ->whereIn('draw_id', $draws)
                    ->where('is_rejected', false)
                    ->count();

                // Get winning bet count - only include bets with receipts in 'placed' status
                $winningBetCount = Bet::placed()->whereIn('teller_id', $tellerIds)
                    ->whereIn('draw_id', $draws)
                    ->where('is_rejected', false)
                    ->whereNotNull('winning_amount')
                    ->where('winning_amount', '>', 0)
                    ->count();

                // Make sure the hits count matches the main view
                if ($coordinatorData) {
                    $coordinatorData['total_hits'] = (int)$coordinatorData['total_hits'];
                }

                // Calculate commission rate
                $commissionRate = 0;
                if ($coordinatorData && $coordinatorData['total_sales'] > 0) {
                    $commissionRate = ($coordinatorData['total_gross'] / $coordinatorData['total_sales']) * 100;
                }

                // Get teller data for the table - using the same calculation as CoordinatorTellerSalesSummary
                $tellerData = [];
                foreach ($tellers as $teller) {
                    // Get all bets for this teller on this date - only include bets with receipts in 'placed' status
                    $betsQuery = Bet::placed()->where('teller_id', $teller->id)
                        ->whereIn('draw_id', $draws)
                        ->where('is_rejected', false);

                    // Calculate totals exactly as in CoordinatorTellerSalesSummary
                    $totalSales = $betsQuery->sum('amount');

                    // Get winning bets amount (hits)
                    $totalHits = $betsQuery->whereNotNull('winning_amount')
                        ->where('winning_amount', '>', 0)
                        ->sum('winning_amount');

                    // Calculate gross as sales - hits
                    $totalGross = $totalSales - $totalHits;

                    if ($totalSales > 0) { // Only include tellers with sales
                        $tellerData[] = [
                            'name' => $teller->name,
                            'sales' => $totalSales,
                            'hits' => $totalHits,
                            'gross' => $totalGross,
                        ];
                    }
                }

                // Sort teller data by sales (highest first)
                usort($tellerData, function($a, $b) {
                    return $b['sales'] <=> $a['sales'];
                });

                return view('livewire.reports.coordinator.coordinator-summary-modal', [
                    'coordinator' => $coordinatorData,
                    'date' => $this->date,
                    'tellerCount' => $tellerCount,
                    'activeTellerCount' => $activeTellerCount,
                    'betCount' => $betCount,
                    'winningBetCount' => $winningBetCount,
                    'tellerData' => $tellerData,
                    'commissionRate' => $commissionRate,
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'))
            ->closeModalByClickingAway(true);
    }

    private function getCoordinatorName($coordinatorId)
    {
        foreach ($this->salesData as $item) {
            if ($item['id'] == $coordinatorId) {
                return $item['name'];
            }
        }

        return 'Coordinator';
    }

    public function viewDetailsAction(): Action
    {
        return Action::make('viewDetails')
            ->label('VIEW DETAILS')
            ->icon('heroicon-o-document-duplicate')
            ->color('indigo')
            ->size('xs')
            ->url(fn (array $arguments) => route('reports.teller-sales-summary', ['coordinator_id' => $arguments['coordinator_id']]))
            ->openUrlInNewTab();
    }

    public function render()
    {
        return view('livewire.reports.coordinator.coordinator-sales-summary');
    }
}
