<?php

namespace App\Livewire\Coordinator;

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
        $this->computeBetRatioStats();

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
        $this->computeBetRatioStats();
        
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
        
        $this->computeBetRatioStats();
    }

    /**
     * Compute bet ratio statistics
     *
     * @return void
     */
    public function computeBetRatioStats()
    {
        // Get the filter date if set, but don't default to today
        $date = $this->filterDate;
        
        // Get the coordinator's location ID
        $locationId = auth()->user()->location_id;
        
        // Query to get bet ratio statistics
        $query = BetRatio::query()
            ->where('location_id', $locationId)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
            })
            ->with(['gameType', 'location', 'user']);
        
        // Get total count and sum of max amounts
        $totalRatios = $query->count();
        $totalMaxAmount = $query->sum('max_amount');
        
        // Get average max amount
        $avgMaxAmount = $totalRatios > 0 ? $totalMaxAmount / $totalRatios : 0;
        
        // Get counts by game type
        $gameTypeCounts = BetRatio::where('location_id', $locationId)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
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
        $locationCounts = BetRatio::where('location_id', $locationId)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
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
        $recentAudits = BetRatioAudit::whereHas('betRatio', function($q) use ($locationId) {
                $q->where('location_id', $locationId);
            })
            ->when($date, function($query, $date) {
                $query->whereHas('betRatio.draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
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
        // Get the coordinator's location ID
        $locationId = auth()->user()->location_id;
        
        return $table
            ->query(BetRatio::query()
                // Always filter by the coordinator's location
                ->where('location_id', $locationId)
                // Only apply date filter if it's explicitly set
                ->when($this->tableFilters['draw_date']['value'] ?? null, function ($query, $date) {
                    $query->whereHas('draw', function ($q) use ($date) {
                        $q->whereDate('draw_date', $date);
                    });
                })
                ->with(['gameType', 'draw', 'user', 'location'])
            )
            ->groups([
                Group::make('gameType.name')
                    ->label('Bet Type')
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
                    ->label('Bet Type')
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
                Tables\Columns\TextColumn::make('sub_selection')
                    ->label('Sub-Selection')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'S2' => 'success',
                        'S3' => 'warning',
                        default => 'gray',
                    })
                    ->visible(fn (?BetRatio $record): bool => $record && $record->gameType && $record->gameType->code === 'D4' && !empty($record->sub_selection))
                    ->sortable(),
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
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->nullable() // Allow clearing the filter
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state;
                                $livewire->computeBetRatioStats();
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
                    ->label('Bet Type')
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
                                Forms\Components\Grid::make(1)
                                    ->schema([
                                        Forms\Components\Select::make('game_type_id')
                                            ->label('Bet Type')
                                            ->relationship('gameType', 'name')
                                            ->required()
                                            ->default($record->game_type_id)
                                            ->live()
                                            ->afterStateUpdated(fn (callable $set) => $set('sub_selection', null)),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('sub_selection')
                                            ->label('Sub-Selection')
                                            ->options([
                                                'S2' => 'S2 (Last 2 Digits)',
                                                'S3' => 'S3 (Last 3 Digits)'
                                            ])
                                            ->nullable()
                                            ->default($record->sub_selection)
                                            ->visible(function (callable $get) use ($record) {
                                                // Get the selected game type ID
                                                $gameTypeId = $get('game_type_id') ?: $record->game_type_id;
                                                if (!$gameTypeId) return false;
                                                
                                                // Get the game type code from the database
                                                $gameType = \App\Models\GameType::find($gameTypeId);
                                                return $gameType && $gameType->code === 'D4';
                                            })
                                            ->helperText('Only for D4 bet type'),
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
                                            ->required()
                                            ->live()
                                            ->mask(function (callable $get) use ($record) {
                                                $gameTypeId = $get('game_type_id') ?: $record->game_type_id;
                                                $subSelection = $get('sub_selection') ?: $record->sub_selection;
                                                if (!$gameTypeId) return '';
                                                
                                                $gameType = \App\Models\GameType::find($gameTypeId);
                                                if (!$gameType) return '';
                                                
                                                return match($gameType->code) {
                                                    'S2' => '99',
                                                    'S3' => '999',
                                                    'D4' => match($subSelection) {
                                                        'S2' => '99',
                                                        'S3' => '999',
                                                        default => '9999'
                                                    },
                                                    default => ''
                                                };
                                            })
                                            ->helperText(function (callable $get) use ($record) {
                                                $gameTypeId = $get('game_type_id') ?: $record->game_type_id;
                                                $subSelection = $get('sub_selection') ?: $record->sub_selection;
                                                if (!$gameTypeId) return 'Enter bet number';
                                                
                                                $gameType = \App\Models\GameType::find($gameTypeId);
                                                if (!$gameType) return 'Enter bet number';
                                                
                                                return match($gameType->code) {
                                                    'S2' => '2-digit number (00-99)',
                                                    'S3' => '3-digit number (000-999)',
                                                    'D4' => match($subSelection) {
                                                        'S2' => '2-digit number (00-99)',
                                                        'S3' => '3-digit number (000-999)',
                                                        default => '4-digit number (0000-9999)'
                                                    },
                                                    default => 'Enter bet number'
                                                };
                                            }),
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
                            $this->computeBetRatioStats();
                            Notification::make()
                                ->title('Bet Ratio Updated')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->computeBetRatioStats();
                            Notification::make()
                                ->title('Bet Ratio Deleted')
                                ->success()
                                ->send();
                        }),
                ])
            ])
            ->headerActions([
                // Tables\Actions\Action::make('add_bet_ratio')
                //     ->label('Add Bet Ratio')
                //     ->icon('heroicon-o-plus')
                //     ->color('primary')
                //     ->form([
                //         Forms\Components\Select::make('draw_id')
                //             ->label('Draw')
                //             ->options(function () {
                //                 return Draw::whereDate('draw_date', $this->filterDate ?: now()->format('Y-m-d'))
                //                     ->get()
                //                     ->mapWithKeys(function ($draw) {
                //                         return [$draw->id => $draw->draw_date . ' - ' . $draw->draw_time];
                //                     });
                //             })
                //             ->required()
                //             ->searchable(),
                //         Forms\Components\Grid::make(1)
                //             ->schema([
                //                 Forms\Components\Select::make('game_type_id')
                //                     ->label('Bet Type')
                //                     ->relationship('gameType', 'name')
                //                     ->required()
                //                     ->live()
                //                     ->afterStateUpdated(fn (callable $set) => $set('sub_selection', null)),
                //             ]),
                //         Forms\Components\Grid::make(2)
                //             ->schema([
                //                 Forms\Components\Select::make('sub_selection')
                //                     ->label('Sub-Selection')
                //                     ->options([
                //                         'S2' => 'S2 (Last 2 Digits)',
                //                         'S3' => 'S3 (Last 3 Digits)'
                //                     ])
                //                     ->nullable()
                //                     ->visible(function (callable $get) {
                //                         // Get the selected game type ID
                //                         $gameTypeId = $get('game_type_id');
                //                         if (!$gameTypeId) return false;
                                        
                //                         // Get the game type code from the database
                //                         $gameType = \App\Models\GameType::find($gameTypeId);
                //                         return $gameType && $gameType->code === 'D4';
                //                     })
                //                     ->helperText('Only for D4 bet type'),
                //                 Forms\Components\Select::make('location_id')
                //                     ->label('Location')
                //                     ->relationship('location', 'name')
                //                     ->required()
                //                     ->searchable(),
                //             ]),
                //         Forms\Components\Grid::make(2)
                //             ->schema([
                //                 Forms\Components\TextInput::make('bet_number')
                //                     ->label('Bet Number')
                //                     ->required()
                //                     ->live()
                //                     ->mask(function (callable $get) {
                //                         $gameTypeId = $get('game_type_id');
                //                         $subSelection = $get('sub_selection');
                //                         if (!$gameTypeId) return '';
                                        
                //                         $gameType = \App\Models\GameType::find($gameTypeId);
                //                         if (!$gameType) return '';
                                        
                //                         return match($gameType->code) {
                //                             'S2' => '99',
                //                             'S3' => '999',
                //                             'D4' => match($subSelection) {
                //                                 'S2' => '99',
                //                                 'S3' => '999',
                //                                 default => '9999'
                //                             },
                //                             default => ''
                //                         };
                //                     })
                //                     ->helperText(function (callable $get) {
                //                         $gameTypeId = $get('game_type_id');
                //                         $subSelection = $get('sub_selection');
                //                         if (!$gameTypeId) return 'Enter bet number';
                                        
                //                         $gameType = \App\Models\GameType::find($gameTypeId);
                //                         if (!$gameType) return 'Enter bet number';
                                        
                //                         return match($gameType->code) {
                //                             'S2' => '2-digit number (00-99)',
                //                             'S3' => '3-digit number (000-999)',
                //                             'D4' => match($subSelection) {
                //                                 'S2' => '2-digit number (00-99)',
                //                                 'S3' => '3-digit number (000-999)',
                //                                 default => '4-digit number (0000-9999)'
                //                             },
                //                             default => 'Enter bet number'
                //                         };
                //                     }),
                //                 Forms\Components\TextInput::make('max_amount')
                //                     ->label('Max Amount')
                //                     ->prefix('₱')
                //                     ->numeric()
                //                     ->required(),
                //             ]),

                //     ])
                //     ->action(function (array $data): void {
                //         $data['user_id'] = auth()->user()->id;
                //         BetRatio::create($data);
                //         $this->computeBetRatioStats();
                //         Notification::make()
                //             ->title('Bet Ratio Added')
                //             ->success()
                //             ->send();
                //     }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->computeBetRatioStats();
                        }),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.coordinator.list-bet-ratios');
    }
}
