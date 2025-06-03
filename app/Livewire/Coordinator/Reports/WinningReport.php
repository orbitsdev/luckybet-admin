<?php

namespace App\Livewire\Coordinator\Reports;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WinningReport extends Component
{
    use WithPagination;

    public $dateRange = 'today';
    public $startDate;
    public $endDate;
    public $selectedDraw = '';
    public $draws = [];
    
    public $totalBets = 0;
    public $totalSales = 0;
    public $winningBets = 0;
    public $totalPayouts = 0;

    public function mount()
    {
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->draws = Draw::all();
        $this->applyFilters();
    }

    public function updatedDateRange()
    {
        switch ($this->dateRange) {
            case 'today':
                $this->startDate = Carbon::now()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'last_week':
                $this->startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function applyFilters()
    {
        // Calculate summary statistics
        $this->calculateSummaryStats();
    }

    private function calculateSummaryStats()
    {
        // Get coordinator ID
        $coordinatorId = Auth::id();

        // Get tellers under this coordinator
        $tellerIds = \App\Models\User::where('coordinator_id', $coordinatorId)->pluck('id')->toArray();
        
        // Base query for bets from tellers under this coordinator
        $betsQuery = Bet::whereIn('teller_id', $tellerIds)
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        
        // Apply draw filter if selected
        if ($this->selectedDraw) {
            $betsQuery->where('draw_id', $this->selectedDraw);
        }

        // Calculate total bets and sales
        $this->totalBets = $betsQuery->count();
        $this->totalSales = $betsQuery->sum('amount');

        // Calculate winning bets and payouts
        $winningBetsQuery = clone $betsQuery;
        $winningBetsQuery->where('status', 'won');
        $this->winningBets = $winningBetsQuery->count();
        $this->totalPayouts = $winningBetsQuery->sum('payout');
    }

    public function getWinningNumbersProperty()
    {
        // Get coordinator ID
        $coordinatorId = Auth::id();

        // Get tellers under this coordinator
        $tellerIds = \App\Models\User::where('coordinator_id', $coordinatorId)->pluck('id')->toArray();
        
        // Get results based on date range
        $resultsQuery = Result::with('draw')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
            
        // Apply draw filter if selected
        if ($this->selectedDraw) {
            $resultsQuery->where('draw_id', $this->selectedDraw);
        }
        
        $results = $resultsQuery->get();
        
        // Enhance results with bet statistics
        foreach ($results as $result) {
            // Get bets for this result's draw from tellers under this coordinator
            $betsForDraw = Bet::whereIn('teller_id', $tellerIds)
                ->where('draw_id', $result->draw_id)
                ->whereDate('created_at', Carbon::parse($result->created_at)->toDateString());
            
            $result->total_bets = $betsForDraw->count();
            $result->total_sales = $betsForDraw->sum('amount');
            
            // Get winning bets
            $winningBets = $betsForDraw->where('status', 'won');
            $result->winning_bets = $winningBets->count();
            $result->total_payouts = $winningBets->sum('payout');
        }
        
        return $results->paginate(10);
    }

    public function getWinningBetsListProperty()
    {
        // Get coordinator ID
        $coordinatorId = Auth::id();

        // Get tellers under this coordinator
        $tellerIds = \App\Models\User::where('coordinator_id', $coordinatorId)->pluck('id')->toArray();
        
        // Get winning bets
        $query = Bet::with(['teller', 'draw'])
            ->whereIn('teller_id', $tellerIds)
            ->where('status', 'won')
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
            
        // Apply draw filter if selected
        if ($this->selectedDraw) {
            $query->where('draw_id', $this->selectedDraw);
        }
        
        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.coordinator.reports.winning', [
            'winningNumbers' => $this->winningNumbers,
            'winningBetsList' => $this->winningBetsList,
        ]);
    }
}
