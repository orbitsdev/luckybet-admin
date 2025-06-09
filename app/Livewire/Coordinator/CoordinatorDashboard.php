<?php

namespace App\Livewire\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class CoordinatorDashboard extends Component
{
    public $selectedDate;
    public $totalBets = 0;
    public $totalAmount = 0;
    public $totalWinningAmount = 0;
    public $tellerStats = [];
    public $gameTypeStats = [];
    public $dateStats = [];
    
    public function mount(): void
    {
        // Always set to today's date by default
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }
    
    public function loadStats(): void
    {
        $this->loadTodayStats();
        $this->loadTellerStats();
        $this->loadGameTypeStats();
        $this->loadWeeklyStats();
    }
    
    protected function loadTodayStats(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $coordinatorId = Auth::id();
        
        // Get total bets count and amount for the selected date
        // Using Eloquent with proper relationship queries
        $betStats = Bet::placed()
            ->whereDate('bet_date', $date)
            ->where(function($query) use ($coordinatorId) {
                // Bets placed by tellers directly supervised by this coordinator
                $query->whereExists(function($subquery) use ($coordinatorId) {
                    $subquery->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'bets.teller_id')
                        ->where('users.coordinator_id', $coordinatorId);
                });
            })
            ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, SUM(CASE WHEN is_claimed = 1 THEN winning_amount ELSE 0 END) as total_winning_amount')
            ->first();
        
        $this->totalBets = $betStats->count ?? 0;
        $this->totalAmount = $betStats->total_amount ?? 0;
        $this->totalWinningAmount = $betStats->total_winning_amount ?? 0;
    }
    
    protected function loadTellerStats(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $coordinatorId = Auth::id();
        
        // Get tellers under this coordinator's supervision
        $this->tellerStats = DB::table('users')
            ->join('bets', 'users.id', '=', 'bets.teller_id')
            ->leftJoin('receipts', 'bets.receipt_id', '=', 'receipts.id')
            ->leftJoin('locations', 'bets.location_id', '=', 'locations.id')
            ->where('users.role', 'teller')
            ->where('users.coordinator_id', $coordinatorId) // Only tellers directly supervised by this coordinator
            ->whereDate('bets.bet_date', $date)
            ->where(function($query) {
                $query->where('receipts.status', 'placed')
                      ->orWhereNull('bets.receipt_id');
            })
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(bets.id) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN bets.is_claimed = 1 THEN bets.winning_amount ELSE 0 END) as total_winning_amount')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_amount')
            ->get();
    }
    
    protected function loadGameTypeStats(): void
    {
        $date = Carbon::parse($this->selectedDate);
        $coordinatorId = Auth::id();
        
        // Get game type distribution for bets under this coordinator
        $this->gameTypeStats = DB::table('bets')
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->leftJoin('receipts', 'bets.receipt_id', '=', 'receipts.id')
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->where('users.coordinator_id', $coordinatorId) // Only bets by tellers supervised by this coordinator
            ->whereDate('bets.bet_date', $date)
            ->where(function($query) {
                $query->where('receipts.status', 'placed')
                      ->orWhereNull('bets.receipt_id');
            })
            ->select(
                'game_types.name',
                'game_types.code',
                DB::raw('COUNT(bets.id) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount')
            )
            ->groupBy('game_types.name', 'game_types.code')
            ->orderByDesc('total_amount')
            ->get();
    }
    
    protected function loadWeeklyStats(): void
    {
        $selectedDate = Carbon::parse($this->selectedDate);
        $startOfWeek = $selectedDate->copy()->startOfWeek();
        $endOfWeek = $selectedDate->copy()->endOfWeek();
        $coordinatorId = Auth::id();
        
        // Get daily bet stats for the current week
        $this->dateStats = DB::table('bets')
            ->leftJoin('receipts', 'bets.receipt_id', '=', 'receipts.id')
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->where('users.coordinator_id', $coordinatorId) // Only bets by tellers supervised by this coordinator
            ->whereBetween('bets.bet_date', [$startOfWeek, $endOfWeek])
            ->where(function($query) {
                $query->where('receipts.status', 'placed')
                      ->orWhereNull('bets.receipt_id');
            })
            ->select(
                DB::raw('DATE(bets.bet_date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(bets.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN bets.is_claimed = 1 THEN bets.winning_amount ELSE 0 END) as total_winning_amount')
            )
            ->groupBy(DB::raw('DATE(bets.bet_date)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');
    }
    
    // Method to set date and reload stats
    public function setDate($date): void
    {
        $this->selectedDate = $date;
        $this->loadStats();
    }
    
    // Method to set to today's date
    public function setToday(): void
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }
    
    public function render(): View
    {
        return view('livewire.coordinator.dashboard');
    }
}
