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

        $query = LowWinNumber::query()
            ->whereHas('draw', function ($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->with(['gameType', 'location', 'user']);

        $this->lowWinStats = [
            'total_low_win_numbers' => $query->count(),
            'total_amount' => $query->sum('winning_amount'),
            'by_game_type' => LowWinNumber::query()
                ->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                })
                ->join('game_types', 'low_win_numbers.game_type_id', '=', 'game_types.id')
                ->selectRaw('game_types.name, COUNT(*) as count')
                ->groupBy('game_types.name')
                ->pluck('count', 'name')
                ->toArray(),
            'by_location' => LowWinNumber::query()
                ->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                })
                ->join('locations', 'low_win_numbers.location_id', '=', 'locations.id')
                ->selectRaw('locations.name, COUNT(*) as count')
                ->groupBy('locations.name')
                ->pluck('count', 'name')
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
    }

    /**
     * Define the table configuration
     *
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(LowWinNumber::query()
                ->when($this->filterDate, function ($query, $date) {
                    $query->whereHas('draw', function ($q) use ($date) {
                        $q->whereDate('draw_date', $date);
                    });
                })
                ->with(['gameType', 'draw', 'user', 'location'])
            )
            ->groups([
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
            ])
            ->defaultGroup('location.name')

            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Bet Type')
                    ->formatStateUsing(function ($state, LowWinNumber $record) {
                        // Support for D4 sub-selection display
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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Added By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(30)
                    ->searchable()
                    ->tooltip(function (LowWinNumber $record): string {
                        return $record->reason ?? '';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
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
                                $livewire->computeLowWinStats();
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
                // Add D4 sub-selection filter
                SelectFilter::make('d4_sub_selection')
                    ->label('D4 Sub-Selection')
                    ->options([
                        'S2' => 'S2',
                        'S3' => 'S3',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'], function ($query, $value) {
                            return $query->where('d4_sub_selection', $value);
                        });
                    }),
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
                // Tables\Actions\Action::make('add_low_win')
                //     ->label('Add Low Win Number')
                //     ->icon('heroicon-o-plus')
                //     ->color('primary')
                //     ->form([
                //         Forms\Components\Select::make('draw_id')
                //             ->label('Draw')
                //             ->options(function () {
                //                 return Draw::whereDate('draw_date', $this->filterDate ?: now()->format('Y-m-d'))
                //                     ->get()
                //                     ->mapWithKeys(function ($draw) {
                //                         // Format date as Month Day, Year (e.g., June 10, 2025)
                //                         $formattedDate = date('F j, Y', strtotime($draw->draw_date));
                //                         // Format time as 12-hour format with AM/PM (e.g., 2:30 PM)
                //                         $formattedTime = date('g:i A', strtotime($draw->draw_time));
                //                         // Combine them with a nice separator
                //                         return [$draw->id => "{$formattedDate} at {$formattedTime}"];
                //                     });
                //             })
                //             ->required()
                //             ->searchable(),
                //         Forms\Components\Grid::make(2)
                //             ->schema([
                //                 Forms\Components\Select::make('game_type_id')
                //                     ->label('Bet Type')
                //                     ->relationship('gameType', 'name')
                //                     ->required()
                //                     ->reactive()
                //                     ->afterStateUpdated(fn (callable $set) => $set('d4_sub_selection', null)),
                //                 Forms\Components\Select::make('d4_sub_selection')
                //                     ->label('D4 Sub-Selection')
                //                     ->options([
                //                         'S2' => 'S2',
                //                         'S3' => 'S3',
                //                     ])
                //                     ->visible(function (callable $get) {
                //                         $gameTypeId = $get('game_type_id');
                //                         if (!$gameTypeId) return false;

                //                         $gameType = GameType::find($gameTypeId);
                //                         return $gameType && $gameType->code === 'D4';
                //                     }),
                //             ]),
                //         Forms\Components\Grid::make(2)
                //             ->schema([
                //                 Forms\Components\Select::make('location_id')
                //                     ->label('Location')
                //                     ->options(function () {
                //                         return \App\Models\Location::query()
                //                             ->orderBy('name')
                //                             ->get()
                //                             ->pluck('name', 'id');
                //                     })
                //                     ->required()
                //                     ->searchable(),
                //                 Forms\Components\TextInput::make('bet_number')
                //                     ->label('Number')
                //                     ->required(),
                //             ]),
                //         Forms\Components\TextInput::make('winning_amount')
                //             ->label('Amount')
                //             ->prefix('₱')
                //             ->numeric()
                //             ->required(),
                //         Forms\Components\Textarea::make('reason')
                //             ->label('Reason')
                //             ->required()
                //             ->columnSpan('full'),
                //     ])
                //     ->action(function (array $data): void {
                //         $data['user_id'] = auth()->id();
                //         LowWinNumber::create($data);
                //         $this->computeLowWinStats();
                //         Notification::make()
                //             ->title('Low Win Number Added')
                //             ->success()
                //             ->send();
                //     }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->computeLowWinStats();
                        }),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.low-win-numbers.list-low-win-numbers');
    }
}
