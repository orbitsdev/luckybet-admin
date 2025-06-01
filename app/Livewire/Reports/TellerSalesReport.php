<?php

namespace App\Livewire\Reports;

use App\Models\User;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class TellerSalesReport extends Component
{
    use WithPagination;

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
        return User::where('role', 'teller')
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
            ->withCount([
                'bets as total_sales' => function ($query) {
                    $query->whereDate('created_at', $this->date)
                        ->select(DB::raw('SUM(amount)'));
                },
                'bets as total_hits' => function ($query) {
                    $query->whereDate('created_at', $this->date)
                        ->whereNotNull('winning_amount')
                        ->select(DB::raw('SUM(winning_amount)'));
                },
                'bets as total_commission' => function ($query) {
                    $query->whereDate('created_at', $this->date)
                        ->select(DB::raw('SUM(commission_amount)'));
                },
            ])
            ->orderBy('name')
            ->paginate(10);
    }

    public function viewTellerDetails($tellerId)
    {
        return redirect()->route('reports.teller-bets-report', [
            'teller_id' => $tellerId,
            'date' => $this->date,
        ]);
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
