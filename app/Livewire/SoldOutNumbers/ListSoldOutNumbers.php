<?php

namespace App\Livewire\SoldOutNumbers;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\Draw;
use Filament\Tables;
use Livewire\Component;
use App\Models\BetRatio;
use App\Models\GameType;
use App\Models\Location;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;

class ListSoldOutNumbers extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    /**
     * Register Livewire event listeners using Livewire 3 syntax
     */
    public function __construct()
    {
        // Register event listeners
        $this->listeners = [
            'filament.table.filter' => 'handleFilterChange',
            'filament.table.filters.reset' => 'handleFilterReset',
            'compute-stats' => 'computeStatsListener',
        ];
    }

    /**
     * The currently selected filter date
     *
     * @var string|null
     */
    public $filterDate;
    
    /**
     * Statistics for the currently selected date
     *
     * @var array
     */
    public array $soldOutStats = [];
    
    /**
     * Flag to track if stats computation is in progress
     *
     * @var bool
     */
    protected $computingStats = false;
    
    /**
     * Timestamp of the last stats computation
     *
     * @var int
     */
    protected $lastStatsComputation = 0;

    /**
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        // Set default filter date to today
        if (!$this->filterDate) {
            $this->filterDate = now()->toDateString();
        }
        
        // Compute initial stats
        $this->computeSoldOutStats();
    }

    /**
     * Handle Filament table filter changes
     *
     * @return void
     */
    public function handleFilterChange(): void
    {
        // Get the current filter date or default to today
        $drawDate = $this->tableFilters['draw_date']['value'] ?? now()->toDateString();

        // If the filter was cleared or reset, explicitly set to today
        if (empty($drawDate) || $drawDate === null) {
            $drawDate = now()->toDateString();
            // Update the table filter value to today as well
            $this->tableFilters['draw_date']['value'] = $drawDate;
        }

        // Update filter date
        $this->filterDate = $drawDate;
        
        // Compute stats for the new date
        $this->computeSoldOutStats();

        // Force a refresh to ensure UI is updated
        $this->dispatch('refresh');
    }

    /**
     * Handle explicit filter reset events
     * This is triggered when the user clicks the reset button
     *
     * @return void
     */
    public function handleFilterReset(): void
    {
        // Set filter date to today
        $today = now()->toDateString();
        $this->filterDate = $today;

        // Update the table filter value to today
        if (isset($this->tableFilters['draw_date'])) {
            $this->tableFilters['draw_date']['value'] = $today;
        }
        
        // Compute stats for today
        $this->computeSoldOutStats();

        // Force a refresh of the component
        $this->dispatch('refresh');
    }

    /**
     * Livewire listener for the compute-stats event
     *
     * @return void
     */
    public function computeStatsListener(): void
    {
        $this->computeSoldOutStats();
    }

    /**
     * Compute sold out numbers statistics for the selected date
     *
     * @return void
     */
    public function computeSoldOutStats()
    {
        // Set computing flag to prevent multiple simultaneous computations
        $this->computingStats = true;
        $this->lastStatsComputation = microtime(true);
        
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set

        // Get all bet ratios for the selected date that have reached their maximum amount
        // We need to join with the bets table to check if the sum of bet amounts equals or exceeds the max_amount
        $soldOutNumbers = BetRatio::query()
            ->whereHas('draw', function($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM bets WHERE bets.bet_number = bet_ratios.bet_number AND bets.game_type_id = bet_ratios.game_type_id AND bets.draw_id = bet_ratios.draw_id) >= bet_ratios.max_amount')
            ->with(['gameType', 'location', 'draw'])
            ->get()
            ->map(function ($betRatio) {
                // Calculate the current bet amount for this bet ratio
                $currentAmount = \DB::table('bets')
                    ->where('bet_number', $betRatio->bet_number)
                    ->where('game_type_id', $betRatio->game_type_id)
                    ->where('draw_id', $betRatio->draw_id)
                    ->sum('amount');
                
                $betRatio->current_amount = $currentAmount;
                return $betRatio;
            });

        // Initialize stats
        $totalSoldOut = 0;
        $gameTypeCounts = [];
        $locationCounts = [];

        // Process each bet ratio
        foreach ($soldOutNumbers as $betRatio) {
            $gameTypeName = $betRatio->gameType->name ?? 'Unknown';
            $locationName = $betRatio->location->name ?? 'Unknown';
            
            // Initialize game type stats if not exists
            if (!isset($gameTypeCounts[$gameTypeName])) {
                $gameTypeCounts[$gameTypeName] = [
                    'count' => 0,
                    'numbers' => []
                ];
            }
            
            // Initialize location stats if not exists
            if (!isset($locationCounts[$locationName])) {
                $locationCounts[$locationName] = [
                    'count' => 0,
                    'numbers' => []
                ];
            }
            
            // Increment counts
            $totalSoldOut++;
            $gameTypeCounts[$gameTypeName]['count']++;
            $gameTypeCounts[$gameTypeName]['numbers'][] = $betRatio->bet_number;
            $locationCounts[$locationName]['count']++;
            $locationCounts[$locationName]['numbers'][] = $betRatio->bet_number;
        }

        // Sort game types and locations by count (descending)
        uasort($gameTypeCounts, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        uasort($locationCounts, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // Store computed stats
        $this->soldOutStats = [
            'total_sold_out' => $totalSoldOut,
            'game_type_counts' => $gameTypeCounts,
            'location_counts' => $locationCounts,
        ];

        // Reset computing flag
        $this->computingStats = false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BetRatio::query()
                    // Only show bet ratios where the sum of bet amounts equals or exceeds the max_amount
                    ->whereRaw('(SELECT COALESCE(SUM(amount), 0) FROM bets WHERE bets.bet_number = bet_ratios.bet_number AND bets.game_type_id = bet_ratios.game_type_id AND bets.draw_id = bet_ratios.draw_id) >= bet_ratios.max_amount')
                    ->with(['draw', 'gameType', 'location', 'user'])
                    // Add a subquery to get the current bet amount
                    ->selectRaw('bet_ratios.*, (SELECT COALESCE(SUM(amount), 0) FROM bets WHERE bets.bet_number = bet_ratios.bet_number AND bets.game_type_id = bet_ratios.game_type_id AND bets.draw_id = bet_ratios.draw_id) as current_amount')
            )
            ->emptyStateHeading('No sold out numbers found')
            ->emptyStateDescription('No sold out numbers are available for the selected date.')
            ->emptyStateIcon('heroicon-o-document-chart-bar')
            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_selection')
                    ->label('Sub Selection')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_amount')
                    ->label('Current Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('draw_date')
                    ->label('Draw Date')
                    ->form([
                        DatePicker::make('draw_date')
                            ->label('Draw Date')
                            ->nullable() // Allow clearing the filter
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Always update filterDate when date changes
                                $livewire->filterDate = $state ?? now()->toDateString();
                                // Compute stats immediately when filter changes
                                $livewire->computeSoldOutStats();
                            })
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['draw_date']) {
                            return null;
                        }

                        return 'Date: ' . date('F j, Y', strtotime($data['draw_date']));
                    })
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['draw_date'] ?? null,
                            fn($query, $date) => $query->whereHas('draw', function ($query) use ($date) {
                                $query->whereDate('draw_date', $date);
                            })
                        );
                    }),
                SelectFilter::make('game_type_id')
                    ->label('Game Type')
                    ->options(GameType::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn($query, $gameTypeId) => $query->where('game_type_id', $gameTypeId)
                        );
                    }),
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->options(Location::pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn($query, $locationId) => $query->where('location_id', $locationId)
                        );
                    }),
            ], layout: FiltersLayout::AboveContent);
    }

    public function render(): View
    {
        return view('livewire.sold-out-numbers.list-sold-out-numbers');
    }
}
   