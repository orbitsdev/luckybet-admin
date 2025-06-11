<?php

namespace App\Livewire\BetRatios;

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
use App\Models\BetRatioAudit;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Section;

class ListBetRatios extends Component implements HasForms, HasTable, HasActions
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
    public array $betRatioStats = [];
    
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
        $this->computeBetRatioStats();
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
        $this->computeBetRatioStats();

        // Force a refresh to ensure UI is updated
        $this->dispatch('refresh');
        
        // Explicitly dispatch an event to update the stats display
        $this->dispatch('stats-updated');
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
        $this->computeBetRatioStats();

        // Force a refresh of the component
        $this->dispatch('refresh');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BetRatio::query()->with(['draw', 'gameType', 'location', 'user']))
            ->emptyStateHeading('No bet ratios found')
            ->emptyStateDescription('No bet ratios are available for the selected date.')
            ->emptyStateIcon('heroicon-o-document-chart-bar')
            ->headerActions([
                CreateAction::make('addBetRatio')
                    ->label('Add Bet Ratio')
                    ->button()
                    ->model(BetRatio::class)
                    ->form([
                        Section::make('Bet Ratio Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('draw_id')
                                            ->label('Draw')
                                            ->options(function () {
                                                return Draw::where('draw_date', $this->filterDate ?? now()->toDateString())
                                                    ->orderBy('draw_time')
                                                    ->get()
                                                    ->pluck('draw_time', 'id')
                                                    ->map(function ($time, $id) {
                                                        return 'Draw at ' . $time;
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),
                                        Select::make('game_type_id')
                                            ->label('Game Type')
                                            ->options(GameType::pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('bet_number')
                                            ->label('Bet Number')
                                            ->required(),
                                        Select::make('sub_selection')
                                            ->label('Sub Selection')
                                            ->options([
                                                'S2' => 'S2',
                                                'S3' => 'S3',
                                            ])
                                            ->nullable(),
                                        TextInput::make('max_amount')
                                            ->label('Max Amount')
                                            ->numeric()
                                            ->required(),
                                    ]),
                                Select::make('location_id')
                                    ->label('Location')
                                    ->options(Location::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])
                    ])
            ])
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
                                $livewire->computeBetRatioStats();
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
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                EditAction::make()
                    ->form([
                        Section::make('Bet Ratio Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('draw_id')
                                            ->label('Draw')
                                            ->options(function (BetRatio $record) {
                                                $drawDate = $record->draw->draw_date;
                                                return Draw::whereDate('draw_date', $drawDate)
                                                    ->orderBy('draw_time')
                                                    ->get()
                                                    ->pluck('draw_time', 'id')
                                                    ->map(function ($time, $id) {
                                                        return 'Draw at ' . $time;
                                                    });
                                            })
                                            ->searchable()
                                            ->required(),
                                        Select::make('game_type_id')
                                            ->label('Game Type')
                                            ->options(GameType::pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('bet_number')
                                            ->label('Bet Number')
                                            ->required(),
                                        Select::make('sub_selection')
                                            ->label('Sub Selection')
                                            ->options([
                                                'S2' => 'S2',
                                                'S3' => 'S3',
                                            ])
                                            ->nullable(),
                                        TextInput::make('max_amount')
                                            ->label('Max Amount')
                                            ->numeric()
                                            ->required(),
                                    ]),
                                Select::make('location_id')
                                    ->label('Location')
                                    ->options(Location::pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ])
                    ]),
                ActionGroup::make([
                    Action::make('viewAudit')
                        ->label('View Audit History')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->modalHeading(fn (BetRatio $record) => 'Audit History - Bet Ratio #' . $record->id)
                        ->modalContent(function (BetRatio $record) {
                            return view('livewire.bet-ratios.partials.audit-history-modal', ['betRatio' => $record->load('betRatioAudit.user')]);
                        })
                        ->modalWidth('3xl')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn ($action) => $action->label('Close'))
                        ->disabledForm(),
                    DeleteAction::make(),
                ]),
            ]);
    }

    /**
     * Refresh the table data
     *
     * @return void
     */
    public function refreshTable(): void
    {
        // This will force Filament to re-query the table data
        $this->resetTable();
        
        // Recompute stats
        $this->computeBetRatioStats();
    }

    /**
     * Compute bet ratio statistics for the selected date
     *
     * @return void
     */
    public function computeBetRatioStats()
    {
        // Set computing flag to prevent multiple simultaneous computations
        $this->computingStats = true;
        $this->lastStatsComputation = microtime(true);
        
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Get all draws for the selected date
        $draws = Draw::where('draw_date', $date)->with('betRatios.gameType', 'betRatios.location')->get();
        
        // Initialize stats arrays
        $totalBetRatios = 0;
        $totalMaxAmount = 0;
        $gameTypeStats = [];
        $locationStats = [];
        $drawTimeStats = [];
        
        // Get all game types for consistent display
        $gameTypes = GameType::all()->pluck('name', 'id')->toArray();
        
        // Process each draw and its bet ratios
        foreach ($draws as $draw) {
            $drawTime = $draw->draw_time;
            
            // Initialize draw time stats if not exists
            if (!isset($drawTimeStats[$drawTime])) {
                $drawTimeStats[$drawTime] = [
                    'total' => 0,
                    'total_max_amount' => 0,
                    'game_types' => [],
                ];
            }
            
            foreach ($draw->betRatios as $betRatio) {
                $totalBetRatios++;
                $totalMaxAmount += $betRatio->max_amount;
                
                $gameTypeId = $betRatio->game_type_id;
                $gameTypeName = $betRatio->gameType->name ?? 'Unknown';
                $locationId = $betRatio->location_id;
                $locationName = $betRatio->location->name ?? 'Unknown';
                
                // Initialize game type stats if not exists
                if (!isset($gameTypeStats[$gameTypeId])) {
                    $gameTypeStats[$gameTypeId] = [
                        'name' => $gameTypeName,
                        'total' => 0,
                        'total_max_amount' => 0,
                        'locations' => [],
                    ];
                }
                
                // Initialize location stats if not exists
                if (!isset($locationStats[$locationId])) {
                    $locationStats[$locationId] = [
                        'name' => $locationName,
                        'total' => 0,
                        'total_max_amount' => 0,
                        'game_types' => [],
                    ];
                }
                
                // Update game type stats
                $gameTypeStats[$gameTypeId]['total']++;
                $gameTypeStats[$gameTypeId]['total_max_amount'] += $betRatio->max_amount;
                
                // Update location stats within game type
                if (!isset($gameTypeStats[$gameTypeId]['locations'][$locationId])) {
                    $gameTypeStats[$gameTypeId]['locations'][$locationId] = [
                        'name' => $locationName,
                        'total' => 0,
                        'total_max_amount' => 0,
                    ];
                }
                $gameTypeStats[$gameTypeId]['locations'][$locationId]['total']++;
                $gameTypeStats[$gameTypeId]['locations'][$locationId]['total_max_amount'] += $betRatio->max_amount;
                
                // Update location stats
                $locationStats[$locationId]['total']++;
                $locationStats[$locationId]['total_max_amount'] += $betRatio->max_amount;
                
                // Update game type stats within location
                if (!isset($locationStats[$locationId]['game_types'][$gameTypeId])) {
                    $locationStats[$locationId]['game_types'][$gameTypeId] = [
                        'name' => $gameTypeName,
                        'total' => 0,
                        'total_max_amount' => 0,
                    ];
                }
                $locationStats[$locationId]['game_types'][$gameTypeId]['total']++;
                $locationStats[$locationId]['game_types'][$gameTypeId]['total_max_amount'] += $betRatio->max_amount;
                
                // Update draw time stats
                $drawTimeStats[$drawTime]['total']++;
                $drawTimeStats[$drawTime]['total_max_amount'] += $betRatio->max_amount;
                
                // Update game type stats within draw time
                if (!isset($drawTimeStats[$drawTime]['game_types'][$gameTypeId])) {
                    $drawTimeStats[$drawTime]['game_types'][$gameTypeId] = [
                        'name' => $gameTypeName,
                        'total' => 0,
                        'total_max_amount' => 0,
                    ];
                }
                $drawTimeStats[$drawTime]['game_types'][$gameTypeId]['total']++;
                $drawTimeStats[$drawTime]['game_types'][$gameTypeId]['total_max_amount'] += $betRatio->max_amount;
            }
        }
        
        // Store computed stats
        $this->betRatioStats = [
            'total_bet_ratios' => $totalBetRatios,
            'total_max_amount' => $totalMaxAmount,
            'game_type_stats' => $gameTypeStats,
            'location_stats' => $locationStats,
            'draw_time_stats' => $drawTimeStats,
            'game_types' => $gameTypes,
        ];
        
        // Reset computing flag
        $this->computingStats = false;
    }
    
    /**
     * Livewire listener for the compute-stats event
     *
     * @return void
     */
    public function computeStatsListener(): void
    {
        $this->computeBetRatioStats();
    }
    
    public function render(): View
    {
        return view('livewire.bet-ratios.list-bet-ratios');
    }
}
