<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Models\Draw;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use App\Services\AdminStatisticsService;

class TellerSalesReport extends Component implements HasForms, HasActions
{
    use WithPagination;
    use InteractsWithForms;
    use InteractsWithActions;

    public $search = '';
    public $location_id = '';
    public $coordinator_id = '';
    public $date = '';
    public $showFilters = true;
    public $locations = [];
    public $coordinators = [];
    public $readyToPrint = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->locations = Location::orderBy('name')->get();
        $this->coordinators = User::where('role', 'coordinator')->orderBy('name')->get();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLocationId()
    {
        $this->resetPage();
        if ($this->location_id) {
            $this->coordinators = User::where('role', 'coordinator')
                ->where('location_id', $this->location_id)
                ->orderBy('name')
                ->get();
        } else {
            $this->coordinators = User::where('role', 'coordinator')->orderBy('name')->get();
        }
        $this->coordinator_id = '';
    }

    public function updatedCoordinatorId()
    {
        $this->resetPage();
    }

    public function updatedDate()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->location_id = '';
        $this->coordinator_id = '';
        $this->date = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function prepareToPrint()
    {
        $this->readyToPrint = true;
    }

    public function getTellersProperty()
    {
        // Get base teller query
        $tellersQuery = User::where('role', 'teller')
            ->with(['coordinator', 'location'])
            ->when($this->search, function ($query) {
                $query->where(function (Builder $query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->location_id, function ($query) {
                $query->where('location_id', $this->location_id);
            })
            ->when($this->coordinator_id, function ($query) {
                $query->where('coordinator_id', $this->coordinator_id);
            })
            ->orderBy('name');

        // Get teller IDs for statistics
        $tellerIds = $tellersQuery->pluck('id')->toArray();

        // Get valid draws for the date
        $draws = Draw::with('result')
            ->whereDate('draw_date', $this->date)
            ->get();

        // Filter for draws with complete results
        $validDraws = $draws->filter(function ($draw) {
            if (!$draw->result) return false;
            return $draw->result->s2_winning_number && 
                   $draw->result->s3_winning_number && 
                   $draw->result->d4_winning_number;
        });

        $drawIds = $validDraws->pluck('id')->toArray();

        // Get sales data using AdminStatisticsService
        $reportService = new AdminStatisticsService();
        $salesData = $reportService->summarizeByTellers($tellerIds, $drawIds);

        // Map sales data to tellers
        $tellers = $tellersQuery->paginate(10);
        foreach ($tellers as $teller) {
            $tellerStats = collect($salesData)->firstWhere('id', $teller->id) ?? [
                'total_sales' => 0,
                'total_hits' => 0,
                'total_commission' => 0,
            ];
            
            $teller->total_sales = $tellerStats['total_sales'] ?? 0;
            $teller->total_hits = $tellerStats['total_hits'] ?? 0;
            $teller->total_commission = $tellerStats['commission'] ?? 0;
        }

        return $tellers;
    }

    public function viewTellerDetailsAction(): Action
    {
        return Action::make('viewTellerDetails')
            ->label('VIEW DETAILS')
            ->icon('heroicon-o-eye')
            ->color('indigo')
            ->size('xs')
            ->url(fn (array $arguments) => route('reports.teller-bets-report', ['teller_id' => $arguments['teller_id'], 'date' => $this->date]));
    }

    public function viewTellerBetsAction(): Action
    {
        return Action::make('viewTellerBets')
            ->label('VIEW BETS')
            ->icon('heroicon-o-currency-dollar')
            ->color('gray')
            ->size('xs')
            ->url(fn (array $arguments) => route('reports.teller-bets-report', ['teller_id' => $arguments['teller_id'], 'date' => $this->date]))
            ->openUrlInNewTab();
    }

    public function render()
    {
        return view('livewire.reports.teller-sales-report', [
            'tellers' => $this->tellers,
        ]);
    }
    
    public function layout()
    {
        return 'components.admin';
    }
}
