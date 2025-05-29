<?php

namespace App\Livewire\BetRatios;

use App\Models\BetRatio;
use App\Models\BetRatioAudit;
use App\Models\Draw;
use App\Models\GameType;
use App\Models\Location;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Attributes\On;

class ListBetRatios extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    /**
     * The currently selected filter date
     *
     * @var string|null
     */
    public $filterDate;
    
    /**
     * Statistics for bet ratios
     *
     * @var array
     */
    public array $ratioStats = [];

    /**
     * Register Livewire event listeners using Livewire 3 syntax
     */
    public function __construct()
    {
        // Register event listeners
        $this->listeners = [
            'filament.table.filter' => 'handleFilterChange',
            'filament.table.filters.reset' => 'handleFilterReset',
        ];
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
        
        // Update filter date and recompute stats
        $this->filterDate = $drawDate;
        $this->computeRatioStats();
        
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
        
        // Recompute stats with today's date
        $this->computeRatioStats();
        
        // Force a refresh of the component
        $this->dispatch('refresh');
    }

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
        
        $this->computeRatioStats();
    }

    /**
     * Compute bet ratio statistics
     *
     * @return void
     */
    public function computeRatioStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Query to get bet ratio statistics
        $query = BetRatio::query()
            ->whereHas('draw', function ($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->with(['gameType', 'location', 'user']);
        
        // Get total count and sum of max amounts
        $totalRatios = $query->count();
        $totalMaxAmount = $query->sum('max_amount');
        
        // Get average max amount
        $avgMaxAmount = $totalRatios > 0 ? $totalMaxAmount / $totalRatios : 0;
        
        // Get counts by game type
        $gameTypeCounts = BetRatio::whereHas('draw', function ($q) use ($date) {
                $q->whereDate('draw_date', $date);
            })
            ->join('game_types', 'bet_ratios.game_type_id', '=', 'game_types.id')
            ->select('game_types.name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(bet_ratios.max_amount) as total_amount')
            ->groupBy('game_types.name')
            ->get()
            ->keyBy('name')
            ->toArray();
        
        // Get counts by location
        $locationCounts = BetRatio::whereHas('draw', function ($q) use ($date) {
                $q->whereDate('draw_date', $date);
            })
            ->join('locations', 'bet_ratios.location_id', '=', 'locations.id')
            ->select('locations.name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(bet_ratios.max_amount) as total_amount')
            ->groupBy('locations.name')
            ->get()
            ->keyBy('name')
            ->toArray();
        
        // Get recent audit history
        $recentAudits = BetRatioAudit::whereHas('betRatio.draw', function ($q) use ($date) {
                $q->whereDate('draw_date', $date);
            })
            ->with(['betRatio.gameType', 'betRatio.location'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $this->ratioStats = [
            'total_ratios' => $totalRatios,
            'total_max_amount' => $totalMaxAmount,
            'avg_max_amount' => $avgMaxAmount,
            'game_type_counts' => $gameTypeCounts,
            'location_counts' => $locationCounts,
            'recent_audits' => $recentAudits,
        ];
    }

    /**
     * Handle the refresh event
     * 
     * @return void
     */
    #[On('refresh')]
    public function refresh(): void
    {
        // This method will be automatically called when the 'refresh' event is dispatched
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BetRatio::query()
                ->when($this->filterDate, function ($query, $date) {
                    $query->whereHas('draw', function ($q) use ($date) {
                        $q->whereDate('draw_date', $date);
                    });
                })
                ->with(['gameType', 'draw', 'user', 'location'])
            )
            ->groups([
                Group::make('gameType.name')
                    ->label('Game Type')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible(),
            ])
            ->defaultGroup('gameType.name')
            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'S2' => 'success',
                        'S3' => 'warning',
                        'D4' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Added By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('draw_date')
                    ->label('Draw Date')
                    ->form([
                        Forms\Components\DatePicker::make('draw_date')
                            ->label('Draw Date')
                            ->nullable() // Allow clearing the filter
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Always update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state ?? now()->toDateString();
                                $livewire->computeRatioStats();
                            })
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['draw_date']) {
                            return null;
                        }
                        
                        return 'Date: ' . date('F j, Y', strtotime($data['draw_date']));
                    })
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['draw_date'] ?? null, function ($q, $date) {
                            return $q->whereHas('draw', function ($subquery) use ($date) {
                                $subquery->whereDate('draw_date', $date);
                            });
                        });
                    }),
                SelectFilter::make('game_type_id')
                    ->label('Game Type')
                    ->relationship('gameType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
            ],
            layout: FiltersLayout::AboveContent
            )
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->form(function (BetRatio $record) {
                            return [
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('game_type_id')
                                            ->label('Game Type')
                                            ->relationship('gameType', 'name')
                                            ->required()
                                            ->default($record->game_type_id),
                                        Forms\Components\Select::make('location_id')
                                            ->label('Location')
                                            ->relationship('location', 'name')
                                            ->required()
                                            ->default($record->location_id),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('bet_number')
                                            ->label('Bet Number')
                                            ->required(),
                                        Forms\Components\TextInput::make('max_amount')
                                            ->label('Max Amount')
                                            ->prefix('₱')
                                            ->numeric()
                                            ->required(),
                                    ]),
                            ];
                        })
                        ->action(function (BetRatio $record, array $data): void {
                            $record->update($data);
                            $this->computeRatioStats();
                            Notification::make()
                                ->title('Bet Ratio Updated')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->computeRatioStats();
                            Notification::make()
                                ->title('Bet Ratio Deleted')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_bet_ratio')
                    ->label('Add Bet Ratio')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('draw_id')
                            ->label('Draw')
                            ->options(function () {
                                return Draw::whereDate('draw_date', $this->filterDate ?: now()->format('Y-m-d'))
                                    ->get()
                                    ->mapWithKeys(function ($draw) {
                                        return [$draw->id => $draw->draw_date . ' - ' . $draw->draw_time];
                                    });
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('game_type_id')
                                    ->label('Game Type')
                                    ->relationship('gameType', 'name')
                                    ->required(),
                                Forms\Components\Select::make('location_id')
                                    ->label('Location')
                                    ->relationship('location', 'name')
                                    ->required()
                                    ->searchable(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('bet_number')
                                    ->label('Bet Number')
                                    ->required(),
                                Forms\Components\TextInput::make('max_amount')
                                    ->label('Max Amount')
                                    ->prefix('₱')
                                    ->numeric()
                                    ->required(),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        $data['user_id'] = auth()->id();
                        BetRatio::create($data);
                        $this->computeRatioStats();
                        Notification::make()
                            ->title('Bet Ratio Added')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->computeRatioStats();
                        }),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.bet-ratios.list-bet-ratios');
    }
}
