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

/**
 * WinningReport Component
 * 
 * This component manages the winning report functionality, including:
 * - Filtering winning bets by various criteria
 * - Displaying statistics about winning bets
 * - Exporting winning bet data to CSV
 */
class WinningReport extends Component
{
    use WithPagination;

    /**
     * Filter Properties
     */
    public $selectedDate;
    public $search = '';
    public $selectedTeller = '';
    public $selectedLocation = '';
    public $selectedCoordinator = '';
    public $selectedGameType = '';
    public $selectedD4SubSelection = '';
    public $selectedClaimedStatus = '';
    public $perPage = 20;

    /**
     * Statistics Properties
     */
    public $totalWinAmount = 0;
    public $totalWinners = 0;
    public $winnersByGameType = [];
    public $winnersByLocation = [];

    /**
     * Dropdown Options
     */
    public $gameTypes = [];
    public $locations = [];
    public $coordinators = [];
    public $tellers = [];
    public $d4SubSelections = ['S2', 'S3'];

    /**
     * Initialize the component
     */
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

    /**
     * Data Loading & Statistics Methods
     */
    
    /**
     * Load data and calculate statistics
     */
    public function loadData()
    {
        // Reset pagination when filters change
        $this->resetPage();

        // Calculate statistics
        $this->calculateStatistics();
    }

    /**
     * Calculate statistics for winning bets
     */
    public function calculateStatistics()
    {
        // Using placed scope to only include bets with receipts in 'placed' status
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

    /**
     * Query Building Methods
     */
    
    /**
     * Build the query for fetching winning bets with all applied filters
     * 
     * @return Builder
     */
    protected function getWinningBetsQuery()
    {
        return Bet::placed()->with([
            'draw.result', 'gameType', 'teller.coordinator', 'location', 'customer'
        ])
        ->whereHas('draw.result')
        ->whereDate('bet_date', $this->selectedDate)
        ->where('is_rejected', false)
        // Apply all filters
        ->when($this->selectedTeller, $this->applyTellerFilter())
        ->when($this->selectedLocation, $this->applyLocationFilter())
        ->when($this->selectedCoordinator, $this->applyCoordinatorFilter())
        ->when($this->selectedGameType, $this->applyGameTypeFilter())
        ->when($this->selectedD4SubSelection, $this->applyD4SubSelectionFilter())
        ->when($this->selectedClaimedStatus !== '', $this->applyClaimedStatusFilter())
        ->when($this->search, $this->applySearchFilter())
        // Filter to only include winning bets
        ->whereHas('draw.result', function (Builder $query) {
            $query->whereRaw('1=1'); // Just to ensure the relation exists
        })
        ->where(function (Builder $query) {
            $this->applyWinningConditions($query);
        })
        ->latest();
    }
    
    /**
     * Apply teller filter to query
     */
    private function applyTellerFilter()
    {
        return function($query) {
            $query->where('teller_id', $this->selectedTeller);
        };
    }
    
    /**
     * Apply location filter to query
     */
    private function applyLocationFilter()
    {
        return function($query) {
            $query->where('location_id', $this->selectedLocation);
        };
    }
    
    /**
     * Apply coordinator filter to query
     */
    private function applyCoordinatorFilter()
    {
        return function($query) {
            $query->whereHas('teller', function($sub) {
                $sub->where('coordinator_id', $this->selectedCoordinator);
            });
        };
    }
    
    /**
     * Apply game type filter to query
     */
    private function applyGameTypeFilter()
    {
        return function($query) {
            $query->where('game_type_id', $this->selectedGameType);
        };
    }
    
    /**
     * Apply D4 sub-selection filter to query
     */
    private function applyD4SubSelectionFilter()
    {
        return function($query) {
            $query->where('d4_sub_selection', $this->selectedD4SubSelection);
        };
    }
    
    /**
     * Apply claimed status filter to query
     */
    private function applyClaimedStatusFilter()
    {
        return function($query) {
            $query->where('is_claimed', $this->selectedClaimedStatus == '1');
        };
    }
    
    /**
     * Apply search filter to query
     */
    private function applySearchFilter()
    {
        return function($query) {
            $query->where(function($sub) {
                $sub->where('ticket_id', 'like', '%' . $this->search . '%')
                    ->orWhere('bet_number', 'like', '%' . $this->search . '%');
            });
        };
    }
    
    /**
     * Apply winning conditions to query
     * 
     * @param Builder $query
     */
    private function applyWinningConditions(Builder $query)
    {
        // For S2 game type - exact match
        $query->where(function ($q) {
            $q->where('game_type_id', 1)
              ->whereRaw('EXISTS (SELECT 1 FROM results WHERE results.draw_id = bets.draw_id AND bets.bet_number = results.s2_winning_number)');
        })
        // For S3 game type - exact match
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
    }

    /**
     * Filter Management Methods
     */
    
    /**
     * Handle coordinator filter change and update teller options
     */
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

    /**
     * Handle per page change
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Handle filter property changes
     * 
     * @param string $property
     */
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
    
    /**
     * Reset all filters to default values
     */
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

    /**
     * Export & Rendering Methods
     */
    
    /**
     * Export winning bets data to CSV file
     */
    public function exportToCsv()
    {
        $winningBets = $this->getWinningBetsQuery()->get();

        $filename = 'winning_bets_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filePath = 'exports/' . $filename;

        // Create CSV content
        $csvContent = $this->generateCsvHeader();
        
        foreach ($winningBets as $bet) {
            $csvContent .= $this->formatBetForCsv($bet);
        }

        // Store the file
        Storage::disk('public')->put($filePath, $csvContent);

        // Generate download URL and trigger browser download
        $downloadUrl = Storage::disk('public')->url($filePath);
        $this->dispatch('downloadFile', ['url' => $downloadUrl]);
    }
    
    /**
     * Generate CSV header row
     * 
     * @return string
     */
    private function generateCsvHeader()
    {
        return "Ticket ID,Bet Number,Game Type,D4 Sub-Selection,Bet Date,Location,Teller,Bet Amount,Winning Amount,Claimed\n";
    }
    
    /**
     * Format a bet record for CSV export
     * 
     * @param Bet $bet
     * @return string
     */
    private function formatBetForCsv($bet)
    {
        $claimed = $bet->is_claimed ? 'Yes' : 'No';
        $betDate = Carbon::parse($bet->bet_date)->format('Y-m-d');
        $d4SubSelection = ($bet->gameType->name === 'D4' && $bet->d4_sub_selection) ? $bet->d4_sub_selection : '-';

        return "\"{$bet->ticket_id}\",\"{$bet->bet_number}\",\"{$bet->gameType->name}\",\"{$d4SubSelection}\",\"{$betDate}\",\"{$bet->location->name}\",\"{$bet->teller->name}\",{$bet->bet_amount},{$bet->winning_amount},{$claimed}\n";
    }

    /**
     * Render the component
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $winners = $this->getWinningBetsQuery()->paginate($this->perPage);

        return view('livewire.winning-report', [
            'winners' => $winners,
        ]);
    }
}
