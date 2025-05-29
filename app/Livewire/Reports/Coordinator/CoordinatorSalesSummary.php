<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CoordinatorSalesSummary extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;
    
    public $date;
    public $searchTerm = '';
    public $salesData = [];
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $debugInfo = [];
    
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
        
        $draws = Draw::whereDate('draw_date', $formattedDate)
            ->orderBy('draw_time')
            ->get();
        
        $drawIds = $draws->pluck('id')->toArray();
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
                
                // Get bets for these tellers
                $betsQuery = Bet::whereIn('teller_id', $tellerIds)
                    ->whereIn('draw_id', $drawIds)
                    ->where('is_rejected', false);
                
                // Apply search filter if provided
                if (!empty($this->searchTerm)) {
                    // Search by coordinator name
                    if (stripos($coordinator->name, $this->searchTerm) === false) {
                        continue; // Skip this coordinator if name doesn't match search
                    }
                }
                
                // Calculate totals
                $totalSales = $betsQuery->sum('amount');
                $totalHits = $betsQuery->whereNotNull('winning_amount')->where('winning_amount', '>', 0)->count();
                $totalGross = $betsQuery->sum('winning_amount');
                
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
        
        // Check if there are any bets for these draws and tellers
        $betCount = Bet::whereIn('draw_id', $drawIds)
            ->whereIn('teller_id', $tellerIds)
            ->count();
        
        $this->debugInfo['bet_count'] = $betCount;
        
        // Get all bets for these tellers on this date
        $betsQuery = Bet::whereIn('teller_id', $tellerIds)
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
    
    public function render()
    {
        return view('livewire.reports.coordinator.coordinator-sales-summary');
    }
}
