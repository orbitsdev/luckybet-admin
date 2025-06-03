<?php

namespace App\Livewire\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\GameType;
use App\Models\Location;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class ManageBets extends Component
{
    use WithPagination;
    
    // Filters
    public $date;
    public $teller_id;
    public $game_type_id;
    public $location_id;
    public $search;
    
    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Stats
    public $totalBets = 0;
    public $totalAmount = 0;
    public $totalWinningAmount = 0;
    
    // Modal
    public $showBetDetailsModal = false;
    public $selectedBet = null;
    
    protected $queryString = [
        'date' => ['except' => ''],
        'teller_id' => ['except' => ''],
        'game_type_id' => ['except' => ''],
        'location_id' => ['except' => ''],
        'search' => ['except' => ''],
    ];
    
    public function mount(): void
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingDate()
    {
        $this->resetPage();
    }
    
    public function updatingTellerId()
    {
        $this->resetPage();
    }
    
    public function updatingGameTypeId()
    {
        $this->resetPage();
    }
    
    public function updatingLocationId()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function viewBetDetails($betId)
    {
        $this->selectedBet = Bet::with(['draw', 'gameType', 'teller', 'receipt'])
            ->findOrFail($betId);
        $this->showBetDetailsModal = true;
    }
    
    public function computeBetStats($bets)
    {
        $this->totalBets = $bets->count();
        $this->totalAmount = $bets->sum('amount');
        $this->totalWinningAmount = $bets->sum('winning_amount');
    }
    
    public function render(): View
    {
        $coordinatorId = Auth::id();
        
        // Get tellers managed by this coordinator
        $tellerIds = User::where('coordinator_id', $coordinatorId)
            ->where('role', 'teller')
            ->pluck('id');
            
        // Base query for bets from coordinator's tellers
        $betsQuery = Bet::whereIn('teller_id', $tellerIds)
            ->placed();
            
        // Apply date filter
        if ($this->date) {
            $betsQuery->whereHas('draw', function($query) {
                $query->whereDate('draw_date', $this->date);
            });
        }
        
        // Apply other filters
        if ($this->teller_id) {
            $betsQuery->where('teller_id', $this->teller_id);
        }
        
        if ($this->game_type_id) {
            $betsQuery->where('game_type_id', $this->game_type_id);
        }
        
        if ($this->location_id) {
            $betsQuery->whereHas('teller', function($query) {
                $query->where('location_id', $this->location_id);
            });
        }
        
        if ($this->search) {
            $betsQuery->where(function($query) {
                $query->where('number_combination', 'like', '%' . $this->search . '%')
                    ->orWhere('ticket_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('teller', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
        // Clone query for stats calculation (without pagination)
        $betsForStats = clone $betsQuery;
        $this->computeBetStats($betsForStats->get());
        
        // Get paginated bets with relations
        $bets = $betsQuery->with(['draw', 'gameType', 'teller', 'teller.location'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
            
        // Get tellers for filter dropdown
        $tellers = User::where('coordinator_id', $coordinatorId)
            ->where('role', 'teller')
            ->orderBy('name')
            ->get();
            
        return view('livewire.coordinator.manage-bets', [
            'bets' => $bets,
            'tellers' => $tellers,
            'gameTypes' => GameType::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ])->layout('components.coordinator');
    }
}
