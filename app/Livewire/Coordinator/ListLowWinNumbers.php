<?php

namespace App\Livewire\Coordinator;

use App\Models\LowWinNumber;
use App\Models\GameType;
use App\Models\Location;
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

class ListLowWinNumbers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Statistics for low win numbers
     */
    public $total_low_win_numbers = 0;
    public $filterDate;
    
    /**
     * Initialize component state
     */
    public function mount()
    {
        // Set default filter date to today
        if (!$this->filterDate) {
            $this->filterDate = now()->toDateString();
        }
        
        $this->computeStats();
    }
    
    /**
     * Compute low win numbers statistics
     */
    public function computeStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Get total count of low win numbers for this coordinator's location
        $this->total_low_win_numbers = LowWinNumber::query()
            ->where('location_id', Auth::user()->location_id)
            ->whereHas('draw', function ($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->count();
    }

    public function render(): View
    {
        return view('livewire.coordinator.list-low-win-numbers');
    }

    public function table(Table $table): Table
    {
        // Get the filter date - default to today if not set
        $date = $this->filterDate ?? now()->toDateString();
        $this->filterDate = $date; // Ensure the property is set
        
        return $table
            ->query(
                // Only show low win numbers for locations assigned to the coordinator
                LowWinNumber::query()
                    ->where('location_id', Auth::user()->location_id)
                    ->whereHas('draw', function ($q) use ($date) {
                        $q->whereDate('draw_date', $date);
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('game_type.name')
                    ->label('Game Type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->label('Winning Amount')
                    ->money('PHP')
                    ->sortable(),
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
                                $livewire->computeStats();
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
                        return $query->whereHas('draw', function ($subquery) use ($date) {
                            $subquery->whereDate('draw_date', $date);
                        });
                    }),
                SelectFilter::make('location_id')
                    ->relationship('location', 'name', function (Builder $query) {
                        // Only show the coordinator's location
                        return $query->where('id', Auth::user()->location_id);
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
                Group::make('game_type.name')
                    ->label('Game Type'),

            ])
            ->poll('60s')
            ->defaultGroup('location.name')
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }

    #[On('low-win-number-created')]
    #[On('low-win-number-updated')]
    #[On('low-win-number-deleted')]
    public function refresh(): void
    {
        $this->computeStats();
        $this->refreshTable();
    }
}
