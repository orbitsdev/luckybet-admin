<?php

namespace App\Livewire\Reports;

use App\Models\Bet;
use App\Models\User;
use App\Models\GameType;
use App\Models\Location;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TellerSalesSummary extends Component
{
    public $date = '';
    public $location_id = '';
    public $game_type_id = '';
    public $locations = [];
    public $gameTypes = [];
    public $showFilters = true;
    public $readyToPrint = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->locations = Location::orderBy('name')->get();
        $this->gameTypes = GameType::all();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function updatedLocationId()
    {
        // Reset other filters if needed
    }

    public function updatedGameTypeId()
    {
        // Reset other filters if needed
    }

    public function updatedDate()
    {
        // Reset other filters if needed
    }

    public function resetFilters()
    {
        $this->location_id = '';
        $this->game_type_id = '';
        $this->date = now()->format('Y-m-d');
    }

    public function prepareToPrint()
    {
        $this->readyToPrint = true;
    }

    public function getSummaryDataProperty()
    {
        $query = DB::table('bets')
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->join('locations', 'users.location_id', '=', 'locations.id')
            ->whereDate('bets.created_at', $this->date)
            ->when($this->location_id, function ($query) {
                $query->where('users.location_id', $this->location_id);
            })
            ->when($this->game_type_id, function ($query) {
                $query->where('bets.game_type_id', $this->game_type_id);
            })
            ->select(
                'locations.name as location_name',
                'game_types.name as game_type',
                DB::raw('COUNT(DISTINCT users.id) as teller_count'),
                DB::raw('SUM(bets.amount) as total_sales'),
                DB::raw('SUM(CASE WHEN bets.winning_amount IS NOT NULL THEN bets.winning_amount ELSE 0 END) as total_hits'),
                DB::raw('SUM(bets.commission_amount) as total_commission')
            )
            ->groupBy('locations.name', 'game_types.name')
            ->orderBy('locations.name')
            ->orderBy('game_types.name')
            ->get();

        return $query;
    }

    public function getTotalSalesProperty()
    {
        return $this->summaryData->sum('total_sales');
    }

    public function getTotalHitsProperty()
    {
        return $this->summaryData->sum('total_hits');
    }

    public function getTotalCommissionProperty()
    {
        return $this->summaryData->sum('total_commission');
    }

    public function getTotalGrossProperty()
    {
        return $this->totalSales - $this->totalHits - $this->totalCommission;
    }

    public function render()
    {
        return view('livewire.reports.teller-sales-summary', [
            'summaryData' => $this->summaryData,
            'totalSales' => $this->totalSales,
            'totalHits' => $this->totalHits,
            'totalCommission' => $this->totalCommission,
            'totalGross' => $this->totalGross,
        ]);
    }
    
    public function layout()
    {
        return 'components.admin';
    }
}
