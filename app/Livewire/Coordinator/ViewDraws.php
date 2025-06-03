<?php

namespace App\Livewire\Coordinator;

use App\Models\Draw;
use App\Models\Bet;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;

class ViewDraws extends Component
{
    use WithPagination;
    
    // Filters
    public $date;
    public $draw_time;
    
    // Sorting
    public $sortField = 'draw_date';
    public $sortDirection = 'desc';
    
    // Modal
    public $showDrawDetailsModal = false;
    public $selectedDraw = null;
    public $drawStats = [];
    
    protected $queryString = [
        'date' => ['except' => ''],
        'draw_time' => ['except' => ''],
    ];
    
    public function mount(): void
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }
    
    public function updatingDate()
    {
        $this->resetPage();
    }
    
    public function updatingDrawTime()
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
    
    public function viewDrawDetails($drawId)
    {
        $this->selectedDraw = Draw::findOrFail($drawId);
        $this->computeDrawStats($drawId);
        $this->showDrawDetailsModal = true;
    }
    
    public function computeDrawStats($drawId)
    {
        $coordinatorId = Auth::id();
        
        // Get tellers managed by this coordinator
        $tellerIds = User::where('coordinator_id', $coordinatorId)
            ->where('role', 'teller')
            ->pluck('id');
            
        // Get draw with bets from coordinator's tellers only
        $draw = Draw::with(['bets' => function($query) use ($tellerIds) {
            $query->whereIn('teller_id', $tellerIds)
                  ->placed();
        }, 'bets.gameType'])
        ->findOrFail($drawId);
        
        // Initialize stats
        $stats = [
            'total_bets' => 0,
            'total_amount' => 0,
            'total_winning_amount' => 0,
            'by_game_type' => [],
            'by_teller' => [],
        ];
        
        // Calculate overall stats
        $stats['total_bets'] = $draw->bets->count();
        $stats['total_amount'] = $draw->bets->sum('amount');
        $stats['total_winning_amount'] = $draw->bets->sum('winning_amount');
        
        // Group by game type
        $gameTypeStats = $draw->bets->groupBy(function($bet) {
            // Handle D4 sub-selections
            if ($bet->gameType->code === 'D4' && !empty($bet->d4_sub_selection)) {
                return "D4-{$bet->d4_sub_selection}";
            }
            return $bet->gameType->code;
        });
        
        foreach ($gameTypeStats as $gameType => $bets) {
            $stats['by_game_type'][$gameType] = [
                'count' => $bets->count(),
                'amount' => $bets->sum('amount'),
                'winning_amount' => $bets->sum('winning_amount'),
            ];
        }
        
        // Group by teller
        $tellerStats = $draw->bets->groupBy('teller_id');
        
        foreach ($tellerStats as $tellerId => $bets) {
            $teller = User::find($tellerId);
            if ($teller) {
                $stats['by_teller'][$tellerId] = [
                    'name' => $teller->name,
                    'count' => $bets->count(),
                    'amount' => $bets->sum('amount'),
                    'winning_amount' => $bets->sum('winning_amount'),
                ];
            }
        }
        
        $this->drawStats = $stats;
    }
    
    public function render(): View
    {
        $coordinatorId = Auth::id();
        
        // Get tellers managed by this coordinator
        $tellerIds = User::where('coordinator_id', $coordinatorId)
            ->where('role', 'teller')
            ->pluck('id');
            
        // Get draws that have bets from coordinator's tellers
        $drawsQuery = Draw::whereHas('bets', function($query) use ($tellerIds) {
            $query->whereIn('teller_id', $tellerIds)
                  ->placed();
        });
        
        // Apply date filter
        if ($this->date) {
            $drawsQuery->whereDate('draw_date', $this->date);
        }
        
        // Apply draw time filter
        if ($this->draw_time) {
            $drawsQuery->where('draw_time', $this->draw_time);
        }
        
        // Get draws with bet counts and amounts
        $draws = $drawsQuery->withCount(['bets' => function($query) use ($tellerIds) {
                $query->whereIn('teller_id', $tellerIds)
                      ->placed();
            }])
            ->withSum(['bets' => function($query) use ($tellerIds) {
                $query->whereIn('teller_id', $tellerIds)
                      ->placed();
            }], 'amount')
            ->withSum(['bets' => function($query) use ($tellerIds) {
                $query->whereIn('teller_id', $tellerIds)
                      ->placed();
            }], 'winning_amount')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);
            
        return view('livewire.coordinator.view-draws', [
            'draws' => $draws,
            'drawTimes' => ['2PM', '5PM', '9PM'],
        ]);
    }
}
