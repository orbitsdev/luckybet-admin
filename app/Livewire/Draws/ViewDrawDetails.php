<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use App\Models\Bet;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class ViewDrawDetails extends Component
{
    public ?Draw $draw = null;
    public array $betStats = [];
    public bool $showDetailedStats = false;
    
    public function mount(Draw $draw): void
    {
        $this->draw = $draw;
        
        // Load essential relationships without loading all bets
        $this->draw->load(['betRatios', 'lowWinNumbers', 'result']);
        
        // Calculate bet statistics using efficient DB queries
        $this->calculateBetStats();
    }
    
    public function calculateBetStats(): void
    {
        // Get total counts and sums directly from the database
        $totalStats = DB::table('bets')
            ->where('draw_id', $this->draw->id)
            ->select(
                DB::raw('COUNT(*) as total_bets'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(winning_amount) as total_winning_amount')
            )
            ->first();
            
        // Get game type statistics
        $gameTypeStats = DB::table('bets')
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->where('bets.draw_id', $this->draw->id)
            ->select(
                'game_types.code as game_type',
                'bets.d4_sub_selection as sub_selection',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(bets.amount) as amount')
            )
            ->groupBy('game_types.code', 'bets.d4_sub_selection')
            ->get()
            ->map(function ($item) {
                // Format D4 subtypes
                if ($item->game_type === 'D4' && !empty($item->sub_selection)) {
                    $item->game_type = "D4-{$item->sub_selection}";
                }
                return $item;
            })
            ->keyBy('game_type');
            
        // Get location statistics (limit to top 10)
        $locationStats = DB::table('bets')
            ->join('locations', 'bets.location_id', '=', 'locations.id')
            ->where('bets.draw_id', $this->draw->id)
            ->select(
                'locations.name as location',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(bets.amount) as amount')
            )
            ->groupBy('locations.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->keyBy('location');
            
        // Get teller statistics (limit to top 10)
        $tellerStats = DB::table('bets')
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->where('bets.draw_id', $this->draw->id)
            ->select(
                'users.name as teller',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(bets.amount) as amount')
            )
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->keyBy('teller');
            
        $this->betStats = [
            'total_bets' => $totalStats->total_bets ?? 0,
            'total_amount' => $totalStats->total_amount ?? 0,
            'total_winning_amount' => $totalStats->total_winning_amount ?? 0,
            'game_types' => $gameTypeStats,
            'locations' => $locationStats,
            'tellers' => $tellerStats,
            'has_more_locations' => DB::table('bets')
                ->join('locations', 'bets.location_id', '=', 'locations.id')
                ->where('bets.draw_id', $this->draw->id)
                ->distinct()
                ->count('locations.id') > 10,
            'has_more_tellers' => DB::table('bets')
                ->join('users', 'bets.teller_id', '=', 'users.id')
                ->where('bets.draw_id', $this->draw->id)
                ->distinct()
                ->count('users.id') > 10,
        ];
    }
    
    public function toggleDetailedStats(): void
    {
        $this->showDetailedStats = !$this->showDetailedStats;
    }
    
    public function render(): View
    {
        return view('livewire.draws.view-draw-details');
    }
}
