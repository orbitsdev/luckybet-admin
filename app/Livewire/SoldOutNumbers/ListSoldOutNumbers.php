<?php

namespace App\Livewire\SoldOutNumbers;

use App\Models\BetRatio;
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
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Attributes\On;

class ListSoldOutNumbers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
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
        // Get the current filter date (can be null if cleared)
        $drawDate = $this->tableFilters['draw_date']['value'] ?? null;
        
        // If the filter was cleared, explicitly set to today
        if (empty($drawDate) || $drawDate === null) {
            $drawDate = now()->toDateString();
            // Update the table filter value to today as well
            $this->tableFilters['draw_date']['value'] = $drawDate;
        }
        
        // Update filter date and recompute stats
        $this->filterDate = $drawDate;
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
        
        // Only reset filters when explicitly clicking the reset button
        // This is handled by Filament's built-in reset functionality
        
        // Recompute stats with today's date
        $this->computeSoldOutStats();
        
        // Force a refresh of the component
        $this->dispatch('refresh');
    }

    /**
     * The currently selected filter date
     *
     * @var string|null
     */
    public $filterDate;

    /**
     * Statistics for sold out numbers
     *
     * @var array
     */
    public array $soldOutStats = [];

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

        $this->computeSoldOutStats();
    }

    /**
     * Compute sold out numbers statistics
     *
     * @return void
     */
    public function computeSoldOutStats()
    {
        // Get the filter date if set, default to today if not set
        $date = $this->filterDate ?? now()->toDateString();
        $this->filterDate = $date; // Ensure the property is set

        // Query to get sold out numbers statistics (BetRatio with max_amount = 0)
        $query = BetRatio::query()
            ->where('max_amount', 0)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
            })
            ->with(['gameType', 'location', 'user']);

        // Get total count
        $totalSoldOut = $query->count();

        // Get counts by game type
        $gameTypeCounts = BetRatio::where('max_amount', 0)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
            })
            ->join('game_types', 'bet_ratios.game_type_id', '=', 'game_types.id')
            ->select('game_types.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('game_types.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        // Get counts by location
        $locationCounts = BetRatio::where('max_amount', 0)
            ->when($date, function($query, $date) {
                $query->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                });
            })
            ->join('locations', 'bet_ratios.location_id', '=', 'locations.id')
            ->select('locations.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('locations.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        $this->soldOutStats = [
            'total_sold_out' => $totalSoldOut,
            'game_type_counts' => $gameTypeCounts,
            'location_counts' => $locationCounts,
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
        $this->computeSoldOutStats();
    }
    
    /**
     * Reset all table filters except the date filter
     * This is useful when we want to keep the date filter but clear other filters
     * 
     * @return void
     */
    public function resetTableFiltersExceptDate(): void
    {
        // This method is kept for compatibility but no longer resets filters
        // We want to allow users to keep their bet type and location filters
        
        // Ensure the date filter is properly set
        $currentDateFilter = $this->tableFilters['draw_date']['value'] ?? now()->toDateString();
        $this->filterDate = $currentDateFilter;
        
        // Recompute stats
        $this->computeSoldOutStats();
    }

    public function table(Table $table): Table
    {
        // Get the filter date - default to today if not set
        $date = $this->filterDate ?? now()->toDateString();
        $this->filterDate = $date; // Ensure the property is set
        
        // Debug the date value
        // dd($date, 'Table query date');
        
        return $table
            ->query(BetRatio::query()
                ->where('max_amount', 0) // Only show sold out numbers (max_amount = 0)
                ->whereHas('draw', function ($q) use ($date) {
                    $q->whereDate('draw_date', $date);
                })
                ->with(['gameType', 'draw', 'user', 'location'])
            )
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
                    ->label('Sold Out Number')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('sub_selection')
                    ->label('Subtype')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => $state ?: 'None')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Added By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
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
                            ->live()
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // If the filter was cleared, explicitly set to today
                                if (empty($state) || $state === null) {
                                    $state = now()->toDateString();
                                    // Update the table filter value to today as well
                                    $livewire->tableFilters['draw_date']['value'] = $state;
                                }
                                
                                // Don't reset other filters when date changes
                                // This allows users to keep their bet type and location filters
                                
                                // Update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state;
                                $livewire->computeSoldOutStats();
                                
                                // No need to call resetTableFiltersExceptDate() here as we've already reset the filters above
                            })
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['draw_date']) {
                            return null;
                        }

                        return 'Date: ' . date('F j, Y', strtotime($data['draw_date']));
                    })
                    ->query(function (Builder $query, array $data) {
                        // Get the date from filter data or use the component's filterDate
                        $date = $data['draw_date'] ?? $this->filterDate ?? now()->toDateString();
                        
                        // Store the filter date in the component property to ensure consistency
                        $this->filterDate = $date;
                        
                        // Apply the date filter - this ensures table data matches stats
                        // Use the same query pattern as in the stats calculation
                        return $query->whereHas('draw', function ($subquery) use ($date) {
                            $subquery->whereDate('draw_date', $date);
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
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Remove Sold Out Number')
                    ->modalDescription('Are you sure you want to remove this number from the sold out list? This will allow betting on this number again.')
                    ->successNotificationTitle('Number removed from sold out list')
                    ->action(function (BetRatio $record): void {
                        // Delete the BetRatio record to remove the sold out restriction
                        $record->delete();
                        $this->computeSoldOutStats();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('delete')
                    ->label('Remove Selected')
                    ->color('danger')
                    ->action(function ($records) {
                        // Delete the selected BetRatio records
                        $records->each->delete();
                        $this->computeSoldOutStats();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Remove Selected Sold Out Numbers')
                    ->modalDescription('Are you sure you want to remove these numbers from the sold out list? This will allow betting on these numbers again.'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.sold-out-numbers.list-sold-out-numbers');
    }
}
