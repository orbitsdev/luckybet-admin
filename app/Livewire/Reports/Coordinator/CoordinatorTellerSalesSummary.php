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

class CoordinatorTellerSalesSummary extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use WithPagination;
    use InteractsWithActions;
    
    public $coordinatorId;
    public $date;
    public $coordinatorData = null;
    public $salesData = [];
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $debugInfo = [];
    
    public function mount($coordinator_id = null)
    {   
        $this->coordinatorId = $coordinator_id;
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadCoordinatorData();
        $this->loadSalesData();
    }
    
    public function loadCoordinatorData()
    {
        $user = Auth::user();
        $coordinatorId = null;
        
        // Determine which coordinator's tellers to show
        if ($user->role === 'admin' && $this->coordinatorId) {
            // Admin viewing a specific coordinator's tellers
            $coordinatorId = $this->coordinatorId;
            
            // Get the coordinator data for display
            $coordinator = User::where('id', $coordinatorId)
                ->where('role', 'coordinator')
                ->first();
                
            if (!$coordinator) {
                // Invalid coordinator ID
                return;
            }
            
            // Store coordinator data for display
            $this->coordinatorData = [
                'name' => $coordinator->name,
                'id' => $coordinator->id
            ];
            
            $this->debugInfo['coordinator'] = $this->coordinatorData;
        } else if ($user->role === 'coordinator') {
            // Coordinator viewing their own tellers
            $coordinatorId = $user->id;
            
            $this->coordinatorData = [
                'name' => $user->name,
                'id' => $user->id
            ];
            
            $this->debugInfo['coordinator'] = $this->coordinatorData;
        } else {
            // Fallback to first coordinator if needed
            $coordinator = User::where('role', 'coordinator')->first();
            if ($coordinator) {
                $coordinatorId = $coordinator->id;
                $this->coordinatorData = [
                    'name' => $coordinator->name,
                    'id' => $coordinator->id
                ];
                
                $this->debugInfo['coordinator'] = $this->coordinatorData;
            }
        }
        
        // Store the coordinator ID for use in other methods
        $this->coordinatorId = $coordinatorId;
    }
    
    public function loadSalesData()
    {
        if (!$this->coordinatorId || !$this->coordinatorData) {
            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;
            $this->debugInfo['error'] = 'No coordinator data available';
            return;
        }
        
        // Get all draws for the selected date
        $draws = Draw::where('draw_date', $this->date)
            ->orderBy('draw_time')
            ->get();
        
        $drawIds = $draws->pluck('id')->toArray();
        
        if (empty($drawIds)) {
            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;
            return;
        }
        
        // Get all tellers for this coordinator
        $tellers = User::where('coordinator_id', $this->coordinatorId)
            ->where('role', 'teller')
            ->get();
            
        if ($tellers->isEmpty()) {
            $this->salesData = [];
            $this->totalSales = 0;
            $this->totalHits = 0;
            $this->totalGross = 0;
            return;
        }
        
        $tellerIds = $tellers->pluck('id')->toArray();
        
        // Get all bets for all tellers under this coordinator on this date
        $bets = Bet::whereIn('teller_id', $tellerIds)
            ->whereIn('draw_id', $drawIds)
            ->where('is_rejected', false)
            ->with(['draw', 'gameType', 'teller'])
            ->get();
        
        // Group data by teller
        $salesByTeller = [];
        $this->totalSales = 0;
        $this->totalHits = 0;
        $this->totalGross = 0;
        
        // Initialize data for all tellers
        foreach ($tellers as $teller) {
            $salesByTeller[$teller->id] = [
                'id' => $teller->id,
                'name' => $teller->name,
                'total_sales' => 0,
                'total_hits' => 0,
                'total_gross' => 0,
                'game_types' => []
            ];
        }
        
        // Process all bets and update teller data
        foreach ($bets as $bet) {
            $tellerId = $bet->teller_id;
            
            // Skip if teller not found (shouldn't happen, but just in case)
            if (!isset($salesByTeller[$tellerId])) {
                continue;
            }
            
            $gameTypeId = $bet->game_type_id;
            $gameTypeName = $bet->gameType->name;
            $gameTypeCode = $bet->gameType->code;
            
            // Handle D4 sub-selection
            $displayGameType = $gameTypeCode;
            if ($gameTypeCode === 'D4' && $bet->d4_sub_selection) {
                $displayGameType = "D4-{$bet->d4_sub_selection}";
            }
            
            // Initialize game type data if not exists for this teller
            if (!isset($salesByTeller[$tellerId]['game_types'][$displayGameType])) {
                $salesByTeller[$tellerId]['game_types'][$displayGameType] = [
                    'name' => $gameTypeName . ($bet->d4_sub_selection ? " ({$bet->d4_sub_selection})" : ""),
                    'code' => $displayGameType,
                    'total_sales' => 0,
                    'total_hits' => 0,
                    'total_gross' => 0,
                ];
            }
            
            // Update sales data for this teller
            $salesByTeller[$tellerId]['total_sales'] += $bet->amount;
            $salesByTeller[$tellerId]['game_types'][$displayGameType]['total_sales'] += $bet->amount;
            
            // Update hits and gross if this is a winning bet
            if ($bet->winning_amount > 0) {
                $salesByTeller[$tellerId]['total_hits'] += $bet->winning_amount;
                $salesByTeller[$tellerId]['total_gross'] += $bet->winning_amount;
                $salesByTeller[$tellerId]['game_types'][$displayGameType]['total_hits'] += $bet->winning_amount;
                $salesByTeller[$tellerId]['game_types'][$displayGameType]['total_gross'] += $bet->winning_amount;
            }
        }
        
        // Calculate totals across all tellers
        $this->totalSales = 0;
        $this->totalHits = 0;
        $this->totalGross = 0;
        
        foreach ($salesByTeller as $teller) {
            $this->totalSales += $teller['total_sales'];
            $this->totalHits += $teller['total_hits'];
            $this->totalGross += $teller['total_gross'];
        }
        
        // Remove tellers with no sales
        foreach ($salesByTeller as $tellerId => $teller) {
            if ($teller['total_sales'] == 0) {
                unset($salesByTeller[$tellerId]);
            }
        }
        
        // Convert to indexed array for the view
        $this->salesData = array_values($salesByTeller);
    }
    
    public function updatedDate()
    {
        $this->loadSalesData();
    }
    
    public function viewTellerDetailsAction(): Action
    {
        return Action::make('viewTellerDetails')
            ->label('View Details')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->modalHeading(fn (array $arguments) => 'Sales Details for ' . $this->getTellerName($arguments['teller_id']))
            ->modalWidth('7xl')
            ->modalContent(function (array $arguments) {
                $tellerId = $arguments['teller_id'];
                $tellerData = null;
                
                // Find the teller data in the salesData array
                foreach ($this->salesData as $teller) {
                    if ($teller['id'] == $tellerId) {
                        $tellerData = $teller;
                        break;
                    }
                }
                
                return view('livewire.reports.coordinator.teller-detailed-sales', [
                    'teller' => $tellerData,
                    'date' => $this->date
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'))
            ->closeModalByClickingAway(true);
    }
    

    
    private function getTellerName($tellerId)
    {
        foreach ($this->salesData as $teller) {
            if ($teller['id'] == $tellerId) {
                return $teller['name'];
            }
        }
        
        return 'Teller';
    }
    
    public function render()
    {
        return view('livewire.reports.coordinator.coordinator-teller-sales-summary');
    }
}
