<?php

namespace App\Livewire;

use App\Models\Bet;
use App\Models\GameType;
use App\Models\Location;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WinningReport extends Component
{
    use WithPagination;

    // Filter properties
    public $selectedDate;
    public $search = '';
    public $selectedTeller = '';
    public $selectedLocation = '';
    public $selectedCoordinator = '';
    public $selectedGameType = '';
    public $selectedD4SubSelection = '';
    public $selectedClaimedStatus = '';
    public $perPage = 20;

    // Statistics
    public $totalWinAmount = 0;
    public $totalWinners = 0;
    public $winnersByGameType = [];
    public $winnersByLocation = [];

    // For dropdowns
    public $gameTypes = [];
    public $locations = [];
    public $coordinators = [];
    public $tellers = [];
    public $d4SubSelections = ['S2', 'S3'];

    public function mount()
    {
        // Set default date to today
        $this->selectedDate = now()->toDateString();

        // Load dropdown options
        $this->gameTypes = GameType::all();
        $this->locations = Location::all();
        $this->coordinators = User::where('role', 'coordinator')->get();
        $this->tellers = User::where('role', 'teller')->get();

        // Initial data load
        $this->loadData();
    }

    public function loadData()
    {
        // Reset pagination when filters change
        $this->resetPage();

        // Calculate statistics
        $this->calculateStatistics();
    }

    public function calculateStatistics()
    {
        $winningBets = $this->getWinningBetsQuery()->get();

        $this->totalWinners = $winningBets->count();
        $this->totalWinAmount = $winningBets->sum('winning_amount');

        // Group winners by game type
        $this->winnersByGameType = $winningBets
            ->groupBy('game_type_id')
            ->map(function ($bets, $gameTypeId) {
                $gameType = GameType::find($gameTypeId);
                return [
                    'name' => $gameType ? $gameType->name : 'Unknown',
                    'count' => $bets->count(),
                    'total' => $bets->sum('winning_amount'),
                ];
            })
            ->values()
            ->toArray();

        // Group winners by location
        $this->winnersByLocation = $winningBets
            ->groupBy('location_id')
            ->map(function ($bets, $locationId) {
                $location = Location::find($locationId);
                return [
                    'name' => $location ? $location->name : 'Unknown',
                    'count' => $bets->count(),
                    'total' => $bets->sum('winning_amount'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getWinningBetsQuery()
    {
        return Bet::with([
            'draw.result', 'gameType', 'teller.coordinator', 'location', 'customer'
        ])
        ->whereHas('draw.result')
        ->whereDate('bet_date', $this->selectedDate)
        ->where('is_rejected', false)
        // Filter by teller
        ->when($this->selectedTeller, function($q) {
            $q->where('teller_id', $this->selectedTeller);
        })
        // Filter by location
        ->when($this->selectedLocation, function($q) {
            $q->where('location_id', $this->selectedLocation);
        })
        // Filter by coordinator (filter tellers by coordinator)
        ->when($this->selectedCoordinator, function($q) {
            $q->whereHas('teller', function($sub) {
                $sub->where('coordinator_id', $this->selectedCoordinator);
            });
        })
        // Filter by game type
        ->when($this->selectedGameType, function($q) {
            $q->where('game_type_id', $this->selectedGameType);
        })
        // Filter by D4 sub-selection
        ->when($this->selectedD4SubSelection, function($q) {
            $q->where('d4_sub_selection', $this->selectedD4SubSelection);
        })
        // Filter by claimed status
        ->when($this->selectedClaimedStatus !== '', function($q) {
            $q->where('is_claimed', $this->selectedClaimedStatus === '1');
        })
        // Search
        ->when($this->search, function($q) {
            $q->where(function($sub) {
                $sub->where('ticket_id', 'like', "%{$this->search}%")
                    ->orWhere('bet_number', 'like', "%{$this->search}%");
            });
        })
        // Only include winning bets - we'll use the Bet model's isHit method which correctly handles D4 sub-selections
        ->whereHas('draw.result', function (Builder $query) {
            // We need the result to exist
            $query->whereNotNull('id');
        })
        ->where(function (Builder $query) {
            // For S2 game type
            $query->where(function ($q) {
                $q->where('game_type_id', 1)
                  ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s2_winning_number)');
            })
            // For S3 game type
            ->orWhere(function ($q) {
                $q->where('game_type_id', 2)
                  ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s3_winning_number)');
            })
            // For D4 game type - exact match
            ->orWhere(function ($q) {
                $q->where('game_type_id', 3)
                  ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.d4_winning_number)');
            })
            // For D4-S2 sub-selection - we need to compare the last 2 digits of D4 winning number
            ->orWhere(function ($q) {
                $q->where('game_type_id', 3)
                  ->where('d4_sub_selection', 'S2')
                  ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 2) = LPAD(bets.bet_number, 2, "0"))');
            })
            // For D4-S3 sub-selection - we need to compare the last 3 digits of D4 winning number
            ->orWhere(function ($q) {
                $q->where('game_type_id', 3)
                  ->where('d4_sub_selection', 'S3')
                  ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND 
                                RIGHT(results.d4_winning_number, 3) = LPAD(bets.bet_number, 3, "0"))');
            });
        })
        ->latest();
    }

    public function updatedSelectedCoordinator()
    {
        // Reset teller selection when coordinator changes
        $this->selectedTeller = '';

        // Filter tellers by selected coordinator
        if ($this->selectedCoordinator) {
            $this->tellers = User::where('role', 'teller')
                ->where('coordinator_id', $this->selectedCoordinator)
                ->get();
        } else {
            $this->tellers = User::where('role', 'teller')->get();
        }

        $this->loadData();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, [
            'selectedDate', 'search', 'selectedTeller', 'selectedLocation',
            'selectedCoordinator', 'selectedGameType', 'selectedD4SubSelection',
            'selectedClaimedStatus'
        ])) {
            $this->loadData();
        }
    }
    
    public function resetFilters()
    {
        $this->selectedDate = now()->toDateString();
        $this->search = '';
        $this->selectedTeller = '';
        $this->selectedLocation = '';
        $this->selectedCoordinator = '';
        $this->selectedGameType = '';
        $this->selectedD4SubSelection = '';
        $this->selectedClaimedStatus = '';
        $this->resetPage();
        $this->loadData();
    }

    public function exportToCsv()
    {
        $winningBets = $this->getWinningBetsQuery()->get();

        $filename = 'winning_bets_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        // Create CSV content
        $csvContent = "Ticket ID,Bet Number,Game Type,D4 Sub-Selection,Bet Date,Location,Teller,Bet Amount,Winning Amount,Claimed\n";

        foreach ($winningBets as $bet) {
            $claimed = $bet->is_claimed ? 'Yes' : 'No';
            $betDate = Carbon::parse($bet->bet_date)->format('Y-m-d');
            $d4SubSelection = ($bet->gameType->name === 'D4' && $bet->d4_sub_selection) ? $bet->d4_sub_selection : '-';

            $csvContent .= "\"{$bet->ticket_id}\",\"{$bet->bet_number}\",\"{$bet->gameType->name}\",\"{$d4SubSelection}\",\"{$betDate}\",\"{$bet->location->name}\",\"{$bet->teller->name}\",{$bet->bet_amount},{$bet->winning_amount},{$claimed}\n";
        }

        // Store the file
        Storage::disk('public')->put($filePath, $csvContent);

        // Generate download URL
        $downloadUrl = Storage::disk('public')->url($filePath);

        // Trigger browser download
        $this->dispatch('downloadFile', ['url' => $downloadUrl]);
    }

    public function render()
    {
        $winners = $this->getWinningBetsQuery()->paginate($this->perPage);

        return view('livewire.winning-report', [
            'winners' => $winners,
        ]);
    }
}
