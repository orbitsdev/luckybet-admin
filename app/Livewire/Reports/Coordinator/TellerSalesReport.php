<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\User;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class TellerSalesReport extends Component
{
    use WithPagination;

    public $search = '';
    public $date = '';
    public $showFilters = true;
    public $readyToPrint = false;
    public $coordinatorId;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->coordinatorId = Auth::id(); // Use the authenticated coordinator
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function updatedSearch()
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
            ->where('coordinator_id', $this->coordinatorId) // Only show tellers for this coordinator
            ->with(['coordinator', 'location'])
            ->when($this->search, function ($query) {
                $query->where(function (Builder $query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount([
                'bets as total_sales' => function ($query) {
                    $query->placed()->whereDate('created_at', $this->date)
                        ->select(DB::raw('SUM(amount)'));
                },
                'bets as total_hits' => function ($query) {
                    $query->placed()->whereDate('created_at', $this->date)
                        ->whereNotNull('winning_amount')
                        ->select(DB::raw('SUM(winning_amount)'));
                },
                'bets as total_commission' => function ($query) {
                    $query->placed()->whereDate('created_at', $this->date)
                        ->select(DB::raw('SUM(commission_amount)'));
                },
            ])
            ->orderBy('name')
            ->paginate(10);
    }

    public function viewTellerDetails($tellerId)
    {
        // Use the coordinator-specific route
        return redirect()->route('coordinator.reports.teller-bets-report', [
            'teller_id' => $tellerId,
            'date' => $this->date,
        ]);
    }

    public function render()
    {
        // Get coordinator info for display
        $coordinator = User::find($this->coordinatorId);
        
        return view('livewire.reports.coordinator.teller-sales-report', [
            'tellers' => $this->tellers,
            'coordinator' => $coordinator
        ]);
    }
}
