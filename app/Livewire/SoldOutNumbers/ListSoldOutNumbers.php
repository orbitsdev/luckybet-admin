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
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Query to get sold out numbers statistics (BetRatio with max_amount = 0)
        $query = BetRatio::query()
            ->where('max_amount', 0)
            ->whereHas('draw', function ($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->with(['gameType', 'location', 'user']);
        
        // Get total count
        $totalSoldOut = $query->count();
        
        // Get counts by game type
        $gameTypeCounts = BetRatio::where('max_amount', 0)
            ->whereHas('draw', function ($q) use ($date) {
                $q->whereDate('draw_date', $date);
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
            ->whereHas('draw', function ($q) use ($date) {
                $q->whereDate('draw_date', $date);
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

    public function table(Table $table): Table
    {
        return $table
            ->query(BetRatio::query()
                ->where('max_amount', 0) // Only show sold out numbers (max_amount = 0)
                ->when($this->filterDate, function ($query, $date) {
                    $query->whereHas('draw', function ($q) use ($date) {
                        $q->whereDate('draw_date', $date);
                    });
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
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Always update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state ?? now()->toDateString();
                                $livewire->computeSoldOutStats();
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
