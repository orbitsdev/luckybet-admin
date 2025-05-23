<?php
namespace App\Filament\Pages;

use App\Models\Bet;
use App\Models\Claim;
use App\Models\Result;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;
use App\Filament\Actions\CoordinatorReportAction;
class WinnersReport extends Page
{
    use InteractsWithActions;
    use InteractsWithForms;

    
    // Filter properties
    public $selectedDate;
    public $search = '';
    public $selectedTeller = '';
    public $selectedLocation = '';
    public $selectedCoordinator = '';
    public $selectedGameType = '';
    public $selectedD4SubSelection = '';
    public $selectedClaimedStatus = '';
    public $page = 1;
    public $perPage = 20;

    public $totalWinAmount = 0;
    public $totalWinners = 0;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Winners';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.winners-report';

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    #[Computed]
    public function winners()
    {
        $query = Bet::with([
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
            $q->whereHas('gameType', function($sub) {
                $sub->where('code', $this->selectedGameType);
            });
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
        ->latest();

        // Get all bets for the page, then filter by isHit (winner logic)
        $bets = $query->get()->filter(function($bet) {
            return $bet->isHit();
        });
        $this->totalWinners = $bets->count();
        $this->totalWinAmount = $bets->sum('winning_amount');

        // Paginate manually
        $page = $this->page;
        $perPage = $this->perPage;
        $paginated = new LengthAwarePaginator(
            $bets->forPage($page, $perPage)->values(),
            $bets->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        return $paginated;
    }

    public function updated($property)
    {
        if (in_array($property, [
            'selectedDate', 'search', 'selectedTeller', 'selectedLocation', 'selectedCoordinator', 'selectedGameType', 'selectedD4SubSelection', 'selectedClaimedStatus', 'perPage'
        ])) {
            $this->page = 1;
        }
    }

    public function testAction(): Action
    {
        return Action::make('test')
            ->requiresConfirmation()
            ->action(function () {
                dd('test');
            });
    }

    public function coordinatorReportAction(): Action
    {
        return CoordinatorReportAction::make()
            ->arguments([
                'date' => $this->selectedDate,
            ]);
    }
}
