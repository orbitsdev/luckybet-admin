<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TellerBetsReport extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;
    
    public $tellerId;
    public $tellerName;
    public $date;
    public $filterStatus = 'all'; // all, winners, rejected
    public $filterGameType = 'all';
    public $filterDrawTime = 'all';
    public $searchTerm = '';
    
    public function mount($teller_id, $date = null)
    {
        $this->tellerId = $teller_id;
        $this->date = $date ?? Carbon::today()->format('Y-m-d');
        
        // Get teller information
        $teller = User::find($this->tellerId);
        if ($teller) {
            $this->tellerName = $teller->name;
        }
    }
    
    public function render()
    {
        // Get all draws for the selected date
        $draws = Draw::where('draw_date', $this->date)
            ->orderBy('draw_time')
            ->get();
        
        $drawIds = $draws->pluck('id')->toArray();
        
        // Start building the query
        $betsQuery = Bet::where('teller_id', $this->tellerId)
            ->whereIn('draw_id', $drawIds)
            ->with(['draw', 'gameType']);
        
        // Apply status filter
        if ($this->filterStatus === 'winners') {
            $betsQuery->where('winning_amount', '>', 0);
        } elseif ($this->filterStatus === 'rejected') {
            $betsQuery->where('is_rejected', true);
        } elseif ($this->filterStatus === 'non_winners') {
            $betsQuery->where('winning_amount', 0)->where('is_rejected', false);
        } else {
            // For 'all', we still exclude rejected by default unless specifically requested
            $betsQuery->where('is_rejected', false);
        }
        
        // Apply game type filter
        if ($this->filterGameType !== 'all') {
            $betsQuery->whereHas('gameType', function($query) {
                $query->where('code', $this->filterGameType);
            });
        }
        
        // Apply draw time filter
        if ($this->filterDrawTime !== 'all') {
            $betsQuery->whereHas('draw', function($query) {
                $query->where('draw_time', $this->filterDrawTime);
            });
        }
        
        // Apply search term
        if (!empty($this->searchTerm)) {
            $betsQuery->where('bet_number', 'like', '%' . $this->searchTerm . '%');
        }
        
        // Get the bets with pagination
        $bets = $betsQuery->orderBy('created_at', 'desc')->paginate(20);
        
        // Get available game types for filter
        $gameTypes = $betsQuery->with('gameType')
            ->get()
            ->pluck('gameType.code')
            ->unique()
            ->toArray();
        
        // Get available draw times for filter
        $drawTimes = $draws->pluck('draw_time')->toArray();
        
        // Calculate totals
        $totalAmount = $betsQuery->sum('amount');
        $totalWinnings = $betsQuery->sum('winning_amount');
        
        return view('livewire.reports.coordinator.teller-bets-report', [
            'bets' => $bets,
            'gameTypes' => $gameTypes,
            'drawTimes' => $drawTimes,
            'totalAmount' => $totalAmount,
            'totalWinnings' => $totalWinnings
        ]);
    }
    
    public function updatedFilterStatus()
    {
        $this->resetPage();
    }
    
    public function updatedFilterGameType()
    {
        $this->resetPage();
    }
    
    public function updatedFilterDrawTime()
    {
        $this->resetPage();
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }
    
    public function updatedDate()
    {
        $this->resetPage();
    }
}
