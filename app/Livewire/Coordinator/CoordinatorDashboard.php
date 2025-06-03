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
    public $date;
    public $totalBets = 0;
    public $totalAmount = 0;
    public $totalWinningAmount = 0;
    public $tellerStats = [];
    public $gameTypeStats = [];
    public $dateStats = [];
    
    public function mount(): void
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }
    
    public function loadStats(): void
    {
        $coordinator = Auth::user();
        
        // Get tellers managed by this coordinator
        $tellerIds = User::where('coordinator_id', $coordinator->id)
            ->where('role', 'teller')
            ->pluck('id');
            
        // Calculate total stats for today
        $todayStats = Bet::whereIn('teller_id', $tellerIds)
            ->whereHas('draw', function($query) {
                $query->whereDate('draw_date', $this->date);
            })
            ->placed()
            ->select(
                DB::raw('COUNT(*) as bet_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(winning_amount) as total_winning_amount')
            )
            ->first();
            
        $this->totalBets = $todayStats->bet_count ?? 0;
        $this->totalAmount = $todayStats->total_amount ?? 0;
        $this->totalWinningAmount = $todayStats->total_winning_amount ?? 0;
        
        // Get stats by teller
        $this->tellerStats = Bet::whereIn('teller_id', $tellerIds)
            ->whereHas('draw', function($query) {
                $query->whereDate('draw_date', $this->date);
            })
            ->placed()
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount'),
                DB::raw('SUM(bets.winning_amount) as total_winning_amount')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_amount')
            ->get()
            ->toArray();
            
        // Get stats by game type
        $this->gameTypeStats = Bet::whereIn('teller_id', $tellerIds)
            ->whereHas('draw', function($query) {
                $query->whereDate('draw_date', $this->date);
            })
            ->placed()
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->select(
                'game_types.code',
                'game_types.name',
                'bets.d4_sub_selection',
                DB::raw('COUNT(*) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount')
            )
            ->groupBy('game_types.code', 'game_types.name', 'bets.d4_sub_selection')
            ->get()
            ->map(function($item) {
                // Format D4 subtypes
                if ($item->code === 'D4' && !empty($item->d4_sub_selection)) {
                    $item->code = "D4-{$item->d4_sub_selection}";
                }
                return $item;
            })
            ->toArray();
            
        // Get stats for last 7 days
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();
        
        $this->dateStats = Bet::whereIn('teller_id', $tellerIds)
            ->whereHas('draw', function($query) use ($startDate, $endDate) {
                $query->whereDate('draw_date', '>=', $startDate)
                      ->whereDate('draw_date', '<=', $endDate);
            })
            ->placed()
            ->join('draws', 'bets.draw_id', '=', 'draws.id')
            ->select(
                DB::raw('DATE(draws.draw_date) as date'),
                DB::raw('COUNT(*) as bet_count'),
                DB::raw('SUM(bets.amount) as total_amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();
    }
    
    public function updatedDate(): void
    {
        $this->loadStats();
    }
    
    public function render(): View
    {
        return view('livewire.coordinator.dashboard');
    }
}
