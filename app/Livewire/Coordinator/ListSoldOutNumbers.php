<?php

namespace App\Livewire\Coordinator;

use App\Models\BetRatio;
use App\Models\GameType;
use App\Models\Location;
use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class ListSoldOutNumbers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * The currently selected filter date
     */
    public $filterDate;
    
    /**
     * Statistics for sold out numbers
     */
    public array $soldOutStats = [];
    
    /**
     * Initialize component state
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
            ->whereHas('location', function ($query) {
                $query->whereIn('id', Auth::user()->locations->pluck('id'));
            })
            ->with(['gameType', 'location']);
        
        // Get total count
        $totalSoldOut = $query->count();
        
        // Store the stats
        $this->soldOutStats = [
            'total_sold_out' => $totalSoldOut
        ];
    }

    public function render(): View
    {
        return view('livewire.coordinator.list-sold-out-numbers');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Only show sold out numbers for locations assigned to the coordinator
                BetRatio::query()
                    ->where('max_amount', 0) // Only show sold out numbers (max_amount = 0)
                    ->when($this->filterDate, function ($query, $date) {
                        $query->whereHas('draw', function ($q) use ($date) {
                            $q->whereDate('draw_date', $date);
                        });
                    })
                    ->whereHas('location', function (Builder $query) {
                        $query->whereIn('id', Auth::user()->locations->pluck('id'));
                    })
                    ->with(['gameType', 'draw', 'location'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('draw_date')
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
                SelectFilter::make('location_id')
                    ->relationship('location', 'name', function (Builder $query) {
                        // Only show locations assigned to the coordinator
                        return $query->whereIn('id', Auth::user()->locations->pluck('id'));
                    })
                    ->label('Location')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Game Type')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->groups([
                Group::make('location.name')
                    ->label('Location'),
                Group::make('gameType.name')
                    ->label('Game Type'),
                Group::make('draw.draw_date')
                    ->label('Draw Date'),
            ])
            ->poll('60s')
            ->defaultGroup('location.name')
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }

    #[On('refresh')]
    public function refresh(): void
    {
        $this->computeSoldOutStats();
        $this->refreshTable();
    }
}
