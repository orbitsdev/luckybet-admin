<?php

namespace App\Filament\Pages;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\Claim;
use App\Models\Result;
use Filament\Pages\Page;
use Livewire\Attributes\Url;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use App\Services\DrawReportService;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Action;

class CoordinatorReport extends Page
{
    use InteractsWithActions;
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Coordinator Report';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.coordinator-report';

    public $selectedDate;
    public $dateOptions = [];
    
    #[Url]
    public $search = '';
    
    public $perPage = 10;
    public $page = 1;

    public function mount(): void
    {
        // Get all unique bet dates (using bet_date from bets)
        $this->dateOptions = Bet::selectRaw('DATE(bet_date) as date')
            ->distinct()
            ->orderByDesc('date')
            ->get()
            ->map(function($bet) {
                $date = $bet->date ?? '';
                return [
                    'date' => $date,
                    'label' => $date ? Carbon::parse($date)->format('F j, Y') : 'Unknown Date',
                ];
            })->values()->toArray();

        // Set default to the most recent date
        $this->selectedDate = $this->dateOptions[0]['date'] ?? Carbon::today()->format('Y-m-d');
    }

    public function updatedSelectedDate(): void
    {
        // Reset pagination when date changes
        $this->page = 1;
    }

    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
    }

    #[Computed]
    public function coordinators()
    {
        // Get all coordinators
        $query = User::where('role', 'coordinator')
            ->with(['tellers']);
            
        // Apply search filter if search term is provided
        if (!empty($this->search)) {
            $search = strtolower($this->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $coordinators = $query->get()->map(function($coordinator) {
            // Get all tellers for this coordinator
            $tellers = $coordinator->tellers;
            
            // Calculate sales, hits, and gross for this coordinator on the selected date
            $totalSales = 0;
            $totalHits = 0;
            $totalGross = 0;
            
            // For each teller, calculate their sales for the selected date
            foreach ($tellers as $teller) {
                // Get bets for this teller for the selected bet_date
                $tellerBets = Bet::with(['draw.result', 'gameType'])
                    ->where('teller_id', $teller->id)
                    ->whereDate('bet_date', $this->selectedDate)
                    ->where('is_rejected', false)
                    ->get();
                
                $tellerSales = $tellerBets->sum('amount');

                // Calculate hits using isHit() method for consistency
                $tellerHits = $tellerBets->filter(function($bet) {
                    // Only count as hit if the draw has a result and bet is a winner
                    return $bet->draw && $bet->draw->result && $bet->isHit();
                })->sum('amount');
                
                // Calculate gross (sales minus hits)
                $tellerGross = $tellerSales - $tellerHits;
                
                $totalSales += $tellerSales;
                $totalHits += $tellerHits;
                $totalGross += $tellerGross;
            }
            
            return [
                'id' => $coordinator->id,
                'name' => $coordinator->name,
                'email' => $coordinator->email,
                'teller_count' => $tellers->count(),
                'total_sales' => $totalSales,
                'total_hits' => $totalHits,
                'total_gross' => $totalGross,
            ];
        })->toArray();
        
        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($coordinators);

        // Get items for current page
        $items = array_slice($coordinators, ($page - 1) * $perPage, $perPage);

        // Create a paginator instance
        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginator;
    }
    
    public function getCoordinatorDetailsAction(): Action
    {
        return Action::make('coordinatorDetails')
            ->label('Coordinator Sheet')
            ->icon('heroicon-o-document-chart-bar')
            ->color('warning')
            ->url(function (array $arguments) {
                return route('filament.admin.pages.coordinator-details', [
                    'coordinatorId' => $arguments['coordinatorId'],
                    'date' => $arguments['date']
                ]);
            })
            ->openUrlInNewTab();
    }
    
    public function getTellerSheetAction(): Action
    {
        return Action::make('tellerSheet')
            ->label('Teller Sheet')
            ->icon('heroicon-o-users')
            ->color('primary')
            ->url(function (array $arguments) {
                return route('filament.admin.pages.teller-sales-summary', [
                    'coordinatorId' => $arguments['coordinatorId'],
                    'date' => $arguments['date']
                ]);
            })
            ->openUrlInNewTab();
    }
    
    public function getTallySheetAction(): Action
    {
        return Action::make('tallySheet')
            ->label('Tally Sheet')
            ->icon('heroicon-o-table-cells')
            ->color('success')
            ->url(function (array $arguments) {
                return route('filament.admin.pages.coordinator-details', [
                    'coordinatorId' => $arguments['coordinatorId'],
                    'date' => $arguments['date'],
                    'view' => 'tally'
                ]);
            })
            ->openUrlInNewTab();
    }
}
