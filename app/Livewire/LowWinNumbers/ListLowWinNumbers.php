<?php

namespace App\Livewire\LowWinNumbers;

use App\Models\Draw;
use App\Models\GameType;
use App\Models\Location;
use App\Models\LowWinNumber;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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

class ListLowWinNumbers extends Component implements HasForms, HasTable
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
     * Filter type (start_date, created_at, or draw_date)
     *
     * @var string
     */
    public $filterType = 'start_date';

    /**
     * Active status filter (active, inactive, all)
     * 
     * @var string
     */
    public $activeFilter = 'active';

    /**
     * Scope filter (global, draw, all)
     * 
     * @var string
     */
    public $scopeFilter = 'all';

    /**
     * Statistics for low win numbers
     *
     * @var array
     */
    public array $lowWinStats = [];

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
        $this->computeLowWinStats();

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
        $this->computeLowWinStats();

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

        $this->computeLowWinStats();
    }

    /**
     * Compute low win number statistics
     *
     * @return void
     */
    public function computeLowWinStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set

        $query = LowWinNumber::query();
        
        // Apply active status filter
        switch ($this->activeFilter) {
            case 'active':
                $query->where('low_win_numbers.is_active', true);
                break;
            case 'inactive':
                $query->where('low_win_numbers.is_active', false);
                break;
            case 'all':
                // No filter on is_active
                break;
        }
        
        // Apply scope filter (global vs draw-specific)
        switch ($this->scopeFilter) {
            case 'global':
                $query->whereNull('draw_id');
                break;
            case 'draw':
                $query->whereNotNull('draw_id');
                break;
            case 'all':
                // No filter on draw_id
                break;
        }
        
        // Apply date filtering based on the selected filter type
        $query->where(function ($query) use ($date) {
            switch ($this->filterType) {
                case 'start_date':
                    // Filter by start_date
                    $query->where(function ($q) use ($date) {
                        $q->whereDate('low_win_numbers.start_date', '<=', $date)
                          ->where(function ($q2) use ($date) {
                              $q2->whereDate('low_win_numbers.end_date', '>=', $date)
                                 ->orWhereNull('low_win_numbers.end_date');
                          });
                    });
                    break;
                case 'created_at':
                    // Filter by created_at date
                    $query->whereDate('created_at', $date);
                    break;
                case 'draw_date':
                    // Filter by draw date (if draw_id is not null)
                    $query->whereNotNull('draw_id')
                          ->whereHas('draw', function ($q) use ($date) {
                              $q->whereDate('draw_date', $date);
                          });
                    break;
            }
        })->with(['gameType', 'draw', 'location', 'user']);

        // Calculate active/inactive and global/draw-specific counts
        $activeCount = (clone $query)->where('low_win_numbers.is_active', true)->count();
        $inactiveCount = (clone $query)->where('low_win_numbers.is_active', false)->count();
        $globalCount = (clone $query)->whereNull('low_win_numbers.draw_id')->count();
        $drawSpecificCount = (clone $query)->whereNotNull('low_win_numbers.draw_id')->count();

        // Calculate statistics
        $this->lowWinStats = [
            'total_low_win_numbers' => $query->count(),
            'total_amount' => $query->sum('winning_amount'),
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
            'global_count' => $globalCount,
            'draw_specific_count' => $drawSpecificCount,
            'by_game_type' => (clone $query)
                ->join('game_types', 'low_win_numbers.game_type_id', '=', 'game_types.id')
                ->selectRaw('game_types.code, COUNT(*) as count')
                ->groupBy('game_types.code')
                ->pluck('count', 'code')
                ->toArray(),
            'by_location' => (clone $query)
                ->leftJoin('locations', 'low_win_numbers.location_id', '=', 'locations.id')
                ->selectRaw('COALESCE(locations.name, \'Global\') as location_name, COUNT(*) as count')
                ->groupBy('location_name')
                ->pluck('count', 'location_name')
                ->toArray(),
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
        // No need to do anything here as Livewire will automatically re-render the component
    }

    /**
     * Update statistics when filter date changes
     *
     * @return void
     */
    public function updatedFilterDate()
    {
        $this->computeLowWinStats();

        // Force a refresh to ensure UI is updated
        $this->dispatch('refresh');
    }
    
    /**
     * Update statistics when filter type changes
     *
     * @return void
     */
    public function updatedFilterType()
    {
        $this->computeLowWinStats();
        $this->dispatch('refresh');
    }
    
    /**
     * Update statistics when active filter changes
     *
     * @return void
     */
    public function updatedActiveFilter()
    {
        $this->computeLowWinStats();
        $this->dispatch('refresh');
    }
    
    /**
     * Update statistics when scope filter changes
     *
     * @return void
     */
    public function updatedScopeFilter()
    {
        $this->computeLowWinStats();
        $this->dispatch('refresh');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LowWinNumber::query()
                    ->with(['gameType', 'draw', 'user', 'location'])
                    ->when($this->filterDate, function ($query) {
                        $query->where(function ($query) {
                            switch ($this->filterType) {
                                case 'start_date':
                                    $query->whereDate('start_date', '<=', $this->filterDate)
                                          ->where(function ($q) {
                                              $q->whereDate('end_date', '>=', $this->filterDate)
                                                ->orWhereNull('end_date');
                                          });
                                    break;
                                case 'created_at':
                                    $query->whereDate('created_at', $this->filterDate);
                                    break;
                                case 'draw_date':
                                    $query->whereNotNull('draw_id')
                                          ->whereHas('draw', function ($q) {
                                              $q->whereDate('draw_date', $this->filterDate);
                                          });
                                    break;
                            }
                        });
                    })
                    // Active/inactive filtering is handled by the SelectFilter
                    // No need for redundant filtering here
                    ->when($this->scopeFilter === 'global', fn ($q) => $q->whereNull('draw_id'))
                    ->when($this->scopeFilter === 'draw', fn ($q) => $q->whereNotNull('draw_id'))
            )
     
        
            ->defaultGroup('location.name')

            ->columns([
         
                    Tables\Columns\ToggleColumn::make('is_active')
                        ->label('Active')
                        ->sortable()
                        ->afterStateUpdated(function () {
                            // Recompute stats but don't change the current filter
                            $this->computeLowWinStats();
                            $this->dispatch('refresh');
                        }),
                
                    Tables\Columns\TextColumn::make('start_date')
                        ->label('Start Date')
                        ->date()
                        ->sortable(),
                
                    Tables\Columns\TextColumn::make('end_date')
                        ->label('End Date')
                        ->date()
                        ->sortable(),
                
                    Tables\Columns\TextColumn::make('draw.draw_date')
                        ->label('Draw Date')
                        ->date()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                
                    Tables\Columns\TextColumn::make('draw.draw_time')
                        ->label('Draw Time')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                
                    Tables\Columns\TextColumn::make('gameType.name')
                        ->label('Bet Type')
                        ->formatStateUsing(function ($state, LowWinNumber $record) {
                            if ($record->gameType && $record->gameType->code === 'D4' && $record->d4_sub_selection) {
                                return "D4-{$record->d4_sub_selection}";
                            }
                            return $state;
                        })
                        ->sortable(),
                
                    Tables\Columns\TextColumn::make('bet_number')
                        ->label('Number')
                        ->searchable()
                        ->copyable(),
                
                    Tables\Columns\TextColumn::make('winning_amount')
                        ->label('Amount')
                        ->money('PHP')
                        ->sortable(),
                
                    Tables\Columns\TextColumn::make('location.name')
                        ->label('Location')
                        ->sortable(),
                
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('Added By')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                
                    Tables\Columns\TextColumn::make('reason')
                        ->label('Reason')
                        ->limit(30)
                        ->searchable()
                        ->tooltip(function (LowWinNumber $record): string {
                            return $record->reason ?? '';
                        }),
                
                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Created')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                
                
            ])
            ->filters([
       
                // SelectFilter::make('is_active')
                //     ->label('Status')
                //     ->options([
                //         '1' => 'Active',
                //         '0' => 'Inactive',
                //         '' => 'All',
                //     ])
                //     ->default('1')
                //     ->query(function (Builder $query, array $data) {
                //         if (isset($data['value']) && $data['value'] !== '') {
                //             $query->where('low_win_numbers.is_active', $data['value']);
                //             // Update the component property for statistics
                //             $this->activeFilter = $data['value'] ? 'active' : 'inactive';
                //         } else {
                //             $this->activeFilter = 'all';
                //         }
                //         $this->computeLowWinStats();
                //         return $query;
                //     })
                //     ->indicateUsing(function (array $data): ?string {
                //         if (!isset($data['value']) || $data['value'] === '') {
                //             return null;
                //         }
                        
                //         return 'Status: ' . ($data['value'] ? 'Active' : 'Inactive');
                //     }),
                
                // Scope filter (global vs draw-specific)
                SelectFilter::make('scope')
                    ->label('Scope')
                    ->options([
                        'global' => 'Global Rules',
                        'draw' => 'Draw-Specific',
                        '' => 'All',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value']) && $data['value'] !== '') {
                            if ($data['value'] === 'global') {
                                $query->whereNull('draw_id');
                            } else { // 'draw'
                                $query->whereNotNull('draw_id');
                            }
                            // Update the component property for statistics
                            $this->scopeFilter = $data['value'];
                        } else {
                            $this->scopeFilter = 'all';
                        }
                        $this->computeLowWinStats();
                        return $query;
                    }),
                
                // SelectFilter::make('game_type_id')
                //     ->label('Bet Type')
                //     ->relationship('gameType', 'name')
                //     ->searchable()
                //     ->preload(),
                
                // // Add D4 sub-selection filter
                // SelectFilter::make('d4_sub_selection')
                //     ->label('D4 Sub-Selection')
                //     ->options([
                //         'S2' => 'S2',
                //         'S3' => 'S3',
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         return $query->when($data['value'], function ($query, $value) {
                //             return $query->where('d4_sub_selection', $value);
                //         });
                //     }),
                
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
                    ->form(function (LowWinNumber $record) {
                        return [
                            // Status and date range section
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Active')
                                        ->default($record->is_active ?? true)
                                        ->required(),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\DatePicker::make('start_date')
                                        ->label('Start Date')
                                        ->default($record->start_date)
                                        ->nullable(),
                                    Forms\Components\DatePicker::make('end_date')
                                        ->label('End Date')
                                        ->default($record->end_date)
                                        ->nullable()
                                        ->afterOrEqual('start_date'),
                                ]),
                            // Draw selection (optional)
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Select::make('draw_id')
                                        ->label('Draw (Optional)')
                                        ->relationship('draw', function ($query) {
                                            return $query->orderByDesc('draw_date')->orderByDesc('draw_time');
                                        }, fn ($draw) => date('F j, Y', strtotime($draw->draw_date)) . ' at ' . date('g:i A', strtotime($draw->draw_time)))
                                        ->searchable()
                                        ->preload()
                                        ->nullable()
                                        ->placeholder('Global rule (applies to all draws)'),
                                ]),
                            // Bet details section
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('game_type_id')
                                        ->label('Bet Type')
                                        ->relationship('gameType', 'name')
                                        ->required()
                                        ->default($record->game_type_id)
                                        ->reactive()
                                        ->afterStateUpdated(fn (callable $set) => $set('d4_sub_selection', null)),
                                    Forms\Components\Select::make('d4_sub_selection')
                                        ->label('D4 Sub-Selection')
                                        ->options([
                                            'S2' => 'S2',
                                            'S3' => 'S3',
                                        ])
                                        ->default($record->d4_sub_selection)
                                        ->visible(function (callable $get) use ($record) {
                                            $gameTypeId = $get('game_type_id') ?: $record->game_type_id;
                                            if (!$gameTypeId) return false;

                                            $gameType = GameType::find($gameTypeId);
                                            return $gameType && $gameType->code === 'D4';
                                        }),
                                ]),
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('bet_number')
                                        ->label('Number')
                                        ->required(),
                                    Forms\Components\TextInput::make('winning_amount')
                                        ->label('Amount')
                                        ->prefix('₱')
                                        ->numeric()
                                        ->required(),
                                ]),
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Select::make('location_id')
                                        ->label('Location')
                                        ->relationship('location', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                ]),
                            Forms\Components\Textarea::make('reason')
                                ->label('Reason')
                                ->required()
                                ->columnSpan('full'),
                        ];
                    })
                    ->action(function (LowWinNumber $record, array $data): void {
                        $record->update($data);
                        $this->computeLowWinStats();
                        Notification::make()
                            ->title('Low Win Number Updated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->after(function () {
                        $this->computeLowWinStats();
                        Notification::make()
                            ->title('Low Win Number Deleted')
                            ->success()
                            ->send();
                    }),

                 ])
            ])
            ->headerActions([
                Tables\Actions\Action::make('add_low_win')
                    ->label('Add Low Win Number')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        // Status and date range section
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                ->label('Start Date')
                                ->default(now()->toDateString())
                                ->helperText('Leave empty to apply indefinitely')
                                ->nullable(),    
                                Forms\Components\DatePicker::make('end_date')
                                ->label('End Date')
                                ->nullable()
                                ->helperText('Leave empty to apply indefinitely')
                                ->afterOrEqual('start_date'),
                            ]),
                        // Draw selection (optional)
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('draw_id')
                                    ->label('Draw (Optional)')
                                    ->helperText('Leave empty to create a global rule that applies to all draws')
                                    ->options(function () {
                                        return Draw::whereDate('draw_date', $this->filterDate ?: now()->format('Y-m-d'))
                                            ->get()
                                            ->mapWithKeys(function ($draw) {
                                                // Format date as Month Day, Year (e.g., June 10, 2025)
                                                $formattedDate = date('F j, Y', strtotime($draw->draw_date));
                                                // Format time as 12-hour format with AM/PM (e.g., 2:30 PM)
                                                $formattedTime = date('g:i A', strtotime($draw->draw_time));
                                                // Combine them with a nice separator
                                                return [$draw->id => "{$formattedDate} at {$formattedTime}"];
                                            });
                                    })
                                    ->nullable()
                                    ->placeholder('Global rule (applies to all draws)')
                                    ->searchable(),
                            ]),
                        // Bet details
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('game_type_id')
                                    ->label('Bet Type')
                                    ->relationship('gameType', 'name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('d4_sub_selection', null)),
                                Forms\Components\Select::make('d4_sub_selection')
                                    ->label('D4 Sub-Selection')
                                    ->options([
                                        'S2' => 'S2',
                                        'S3' => 'S3',
                                    ])
                                    ->visible(function (callable $get) {
                                        $gameTypeId = $get('game_type_id');
                                        if (!$gameTypeId) return false;

                                        $gameType = GameType::find($gameTypeId);
                                        return $gameType && $gameType->code === 'D4';
                                    }),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('location_id')
                                    ->label('Location')
                                    ->options(function () {
                                        return \App\Models\Location::query()
                                            ->orderBy('name')
                                            ->get()
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
    ->nullable()  ,
                                Forms\Components\TextInput::make('bet_number')
                                    ->label('Number')
                                    ->required(),
                            ]),
                        Forms\Components\TextInput::make('winning_amount')
                            ->label('Amount')
                            ->prefix('₱')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->columnSpan('full'),
                    ])
                    ->action(function (array $data): void {
                        $data['user_id'] = auth()->id();
                        LowWinNumber::create($data);
                        $this->computeLowWinStats();
                        Notification::make()
                            ->title('Low Win Number Added')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->requiresConfirmation()
                //         ->after(function () {
                //             $this->computeLowWinStats();
                //         }),
                // ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.low-win-numbers.list-low-win-numbers');
    }
}
