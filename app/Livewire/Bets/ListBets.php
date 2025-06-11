<?php

namespace App\Livewire\Bets;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use App\Models\Location;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\On;

class ListBets extends Component implements HasForms, HasTable
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
     * Statistics for the bets
     *
     * @var array
     */
    public array $betStats = [];

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
        $betDate = $this->tableFilters['bet_date']['value'] ?? now()->toDateString();

        // If the filter was cleared or reset, explicitly set to today
        if (empty($betDate) || $betDate === null) {
            $betDate = now()->toDateString();
            // Update the table filter value to today as well
            $this->tableFilters['bet_date']['value'] = $betDate;
        }

        // Update filter date and recompute stats
        $this->filterDate = $betDate;
        $this->computeBetStats();

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
        if (isset($this->tableFilters['bet_date'])) {
            $this->tableFilters['bet_date']['value'] = $today;
        }

        // Recompute stats with today's date
        $this->computeBetStats();

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

        $this->computeBetStats();
    }

    /**
     * Compute bet statistics for the selected date
     *
     * @return void
     */
    public function computeBetStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set

        // Query to get bet statistics - use select('*') to ensure all fields are retrieved
        // Only include bets with receipts in 'placed' status
        $totalBets = Bet::placed()->select('*')->whereDate('bet_date', $date)->count();
        $totalAmount = Bet::placed()->select('*')->whereDate('bet_date', $date)->sum('amount');
        $totalWinningAmount = Bet::placed()->select('*')->whereDate('bet_date', $date)->sum('winning_amount');

        // Get counts by game type - using selectRaw to include all necessary fields
        // Only include bets with receipts in 'placed' status
        $gameTypeCounts = Bet::placed()->whereDate('bet_date', $date)
            ->join('game_types', 'bets.game_type_id', '=', 'game_types.id')
            ->select('game_types.code', 'game_types.name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(bets.amount) as total_amount')
            ->groupBy('game_types.code', 'game_types.name')
            ->get()
            ->keyBy('code')
            ->toArray();

        // Get counts by location - using aggregate functions that don't need the is_claimed attribute
        // Only include bets with receipts in 'placed' status
        $locationCounts = Bet::placed()->whereDate('bet_date', $date)
            ->join('locations', 'bets.location_id', '=', 'locations.id')
            ->select('locations.name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(bets.amount) as total_amount')
            ->groupBy('locations.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        // Get counts by teller - using aggregate functions that don't need the is_claimed attribute
        // Only include bets with receipts in 'placed' status
        $tellerCounts = Bet::placed()->whereDate('bet_date', $date)
            ->join('users', 'bets.teller_id', '=', 'users.id')
            ->select('users.name')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(bets.amount) as total_amount')
            ->groupBy('users.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        $this->betStats = [
            'total_bets' => $totalBets,
            'total_amount' => $totalAmount,
            'total_winning_amount' => $totalWinningAmount,
            'game_type_counts' => $gameTypeCounts,
            'location_counts' => $locationCounts,
            'teller_counts' => $tellerCounts,
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

    public function table(Table $table): Table
    {
        return $table
            ->query(Bet::placed()->select('bets.*')->with(['draw', 'gameType', 'teller', 'location']))
            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.code')
                    ->label('Bet Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'S2' => 'success',
                        'S3' => 'warning',
                        'D4' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('d4_sub_selection')
                    ->label('D4 Sub')
                    ->placeholder('-')
                    ->badge()
                    ->color('purple')
                    ->visible(fn ($livewire) => Bet::placed()->whereNotNull('d4_sub_selection')->exists()),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->label('Winning')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_claimed')
                    ->label('Claimed')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_rejected')
                    ->label('Rejected')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet_date')
                    ->label('Bet Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('claimed_at')
                    ->label('Claimed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('bet_date')
                    ->label('Bet Date')
                    ->form([
                        DatePicker::make('bet_date')
                            ->label('Bet Date')
                            ->nullable() // Allow clearing the filter
                            ->default(fn() => now()->toDateString()) // Default to today dynamically
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Always update filterDate and recompute stats when date changes
                                $livewire->filterDate = $state ?? now()->toDateString();
                                $livewire->computeBetStats();
                            })
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['bet_date']) {
                            return null;
                        }

                        return 'Date: ' . date('F j, Y', strtotime($data['bet_date']));
                    })
                    ->query(fn($query, $data) => $query->when($data['bet_date'] ?? null, fn($q, $date) => $q->whereDate('bet_date', $date))),
                SelectFilter::make('game_type_id')
                    ->label('Bet Type')
                    ->relationship('gameType', 'name'),
                SelectFilter::make('teller_id')
                    ->label('Teller')
                    ->relationship('teller', 'name', fn (Builder $query) => $query->where('role', 'teller'))->searchable()->preload(),
                SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'name'),
                Tables\Filters\SelectFilter::make('is_claimed')
                    ->label('Claimed Status')
                    ->options([
                        '1' => 'Claimed',
                        '0' => 'Not Claimed',
                    ]),
                Tables\Filters\SelectFilter::make('is_rejected')
                    ->label('Rejection Status')
                    ->options([
                        '1' => 'Rejected',
                        '0' => 'Not Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('d4_sub_selection')
                    ->label('D4 Sub-Selection')
                    ->options([
                        'S2' => 'D4-S2',
                        'S3' => 'D4-S3',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function($q, $value) {
                            return $q->where('d4_sub_selection', $value);
                        });
                    }),
            ],
             layout: FiltersLayout::AboveContent

             )
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Group::make('gameType.code')
                    ->label('Bet Type')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Bet $record): string => $record->gameType?->code ?? 'Unknown'),
                Group::make('teller.name')
                    ->label('Teller')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Bet $record): string => $record->teller?->name ?? 'Unknown'),
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Bet $record): string => $record->location?->name ?? 'Unknown'),
            ])
            //default group location
            ->defaultGroup('location.name')
            ;
    }

    public function render(): View
    {
        return view('livewire.bets.list-bets');
    }
}
