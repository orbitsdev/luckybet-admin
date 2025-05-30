<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TellerBetsReport extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use WithPagination;
    use InteractsWithActions;

    public $tellerId;
    public $tellerName;
    public $coordinatorName;
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

            // Get coordinator name
            if ($teller->coordinator) {
                $this->coordinatorName = $teller->coordinator->name;
            } else {
                $this->coordinatorName = 'Unknown Coordinator';
            }
        } else {
            $this->tellerName = 'Unknown Teller';
            $this->coordinatorName = 'Unknown Coordinator';
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
            // Check if this is a D4 sub-selection filter
            if (strpos($this->filterGameType, 'D4-') === 0) {
                $subSelection = substr($this->filterGameType, 3); // Get S2 or S3 part
                $betsQuery->whereHas('gameType', function($query) {
                    $query->where('code', 'D4');
                })->where('d4_sub_selection', $subSelection);
            } else {
                $betsQuery->whereHas('gameType', function($query) {
                    $query->where('code', $this->filterGameType);
                });
            }
        }

        // Apply draw time filter
        if ($this->filterDrawTime !== 'all') {
            $betsQuery->whereHas('draw', function($query) {
                $query->where('draw_time', $this->filterDrawTime);
            });
        }

        // Apply search term for both ticket ID and bet number
        if (!empty($this->searchTerm)) {
            $betsQuery->where(function($query) {
                $query->where('ticket_id', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('bet_number', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Get the bets without pagination first for grouping
        $allFilteredBets = $betsQuery->with(['draw', 'gameType'])->orderBy('created_at', 'desc')->get();

        // Group bets by draw time
        $groupedBets = $allFilteredBets->groupBy(function($bet) {
            return $bet->draw->draw_time;
        });

        // Apply pagination to the grouped result
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        // Flatten the grouped bets for pagination
        $flattenedBets = $allFilteredBets->values();
        $paginatedBets = new \Illuminate\Pagination\LengthAwarePaginator(
            $flattenedBets->slice($offset, $perPage),
            $flattenedBets->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get the bets with standard pagination as backup
        $bets = $paginatedBets;

        // Get all bets for game type filtering (without pagination)
        $allBets = $betsQuery->with(['gameType'])->get();

        // Build game types array including D4 sub-selections
        $gameTypes = [];
        foreach ($allBets as $bet) {
            $gameTypeCode = $bet->gameType->code;
            if (!in_array($gameTypeCode, $gameTypes)) {
                $gameTypes[] = $gameTypeCode;
            }

            // Add D4 sub-selections as separate filter options
            if ($gameTypeCode === 'D4' && $bet->d4_sub_selection) {
                $subSelectionCode = "D4-{$bet->d4_sub_selection}";
                if (!in_array($subSelectionCode, $gameTypes)) {
                    $gameTypes[] = $subSelectionCode;
                }
            }
        }

        // Get available draw times for filter (make them unique)
        $drawTimes = $draws->pluck('draw_time')->unique()->toArray();

        // Calculate totals
        $totalAmount = $betsQuery->sum('amount');
        $totalWinnings = $betsQuery->sum('winning_amount');
        $totalCommission = $betsQuery->sum('commission_amount');
        $totalGross = $totalAmount - $totalWinnings; // Gross = Sales - Hits

        return view('livewire.reports.coordinator.teller-bets-report', [
            'bets' => $bets,
            'groupedBets' => $groupedBets,
            'gameTypes' => $gameTypes,
            'drawTimes' => $drawTimes,
            'totalAmount' => $totalAmount,
            'totalWinnings' => $totalWinnings,
            'totalCommission' => $totalCommission,
            'totalGross' => $totalGross
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

    public function resetFilters()
    {
        $this->filterStatus = 'all';
        $this->filterGameType = 'all';
        $this->filterDrawTime = 'all';
        $this->searchTerm = '';
        $this->resetPage();
    }

    public function viewBetAction(): Action
    {
        return Action::make('viewBet')
            ->label('View Bet')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->size('xs')
            ->modalHeading(fn (array $arguments) => 'Bet Details #' . $arguments['bet_id'])
            ->modalWidth('7xl')
            ->modalContent(function (array $arguments) {
                $betId = $arguments['bet_id'];
                $bet = Bet::with(['draw', 'gameType', 'teller', 'location', 'customer'])->find($betId);

                if (!$bet) {
                    return view('livewire.reports.coordinator.bet-not-found');
                }

                return view('livewire.reports.coordinator.bet-details', [
                    'bet' => $bet
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'))
            ->closeModalByClickingAway(false);
    }
}
