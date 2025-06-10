<?php

namespace App\Livewire\Coordinator;

use App\Models\GameType;
use App\Models\Location;
use App\Models\WinningAmount;
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

class ListWinningAmount extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Statistics for winning amounts
     */
    public array $winningAmountStats = [];

    /**
     * Initialize component state
     */
    public function mount(): void
    {
        $this->computeWinningAmountStats();
    }

    /**
     * Compute winning amount statistics for coordinator's location
     */
    public function computeWinningAmountStats(): void
    {
        // Base query to filter by coordinator's location
        $baseQuery = WinningAmount::query()
            ->whereHas('location', function (Builder $query) {
                $query->where('id', Auth::user()->location_id);
            });
            
        // Get total count of winning amount configurations
        $totalConfigs = (clone $baseQuery)->count();

        // Get counts by game type
        $gameTypeCounts = (clone $baseQuery)
            ->join('game_types', 'winning_amounts.game_type_id', '=', 'game_types.id')
            ->select('game_types.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('game_types.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        // Calculate average winning amount
        $avgWinningAmount = (clone $baseQuery)->avg('amount');

        $this->winningAmountStats = [
            'total_configs' => $totalConfigs,
            'game_type_counts' => $gameTypeCounts,
            'avg_amount' => $avgWinningAmount,
        ];
    }

    public function render(): View
    {
        return view('livewire.coordinator.list-winning-amount', [
            'stats' => $this->winningAmountStats,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Only show winning amounts for locations assigned to the coordinator
                WinningAmount::query()
                    ->whereHas('location', function (Builder $query) {
                        $query->where('id', Auth::user()->location_id);
                    })
                    ->with(['gameType', 'location'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Bet Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'S2' => 'success',
                        'S3' => 'warning',
                        'D4' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
              
                Tables\Columns\TextColumn::make('amount')
                    ->label('Bet Amount')
                    ->money('PHP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->label('Winning Amount')
                    ->money('PHP')
                    ->searchable()
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
                SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Bet Type')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->groups([
                Group::make('gameType.name')
                    ->label('Bet Type')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Model $record): string => $record->gameType?->name ?? 'Unknown Bet Type'),
                Group::make('bet_type')
                    ->label('Bet Type'),
            ])
            ->defaultGroup('gameType.name')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->form(function (WinningAmount $record) {
                            return [
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('game_type_id')
                                            ->label('Bet Type')
                                            ->relationship('gameType', 'name')
                                            ->required()
                                            ->default($record->game_type_id),
                                        Forms\Components\Hidden::make('location_id')
                                            ->default(Auth::user()->location_id),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('amount')
                                            ->label('Bet Amount')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₱')
                                            ->default($record->amount),
                                        Forms\Components\TextInput::make('winning_amount')
                                            ->label('Winning Amount')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₱')
                                            ->default($record->winning_amount)
                                            ->helperText('The amount a player wins when their bet matches the winning number'),
                                    ]),
                            ];
                        })
                        ->action(function (array $data, WinningAmount $record): void {
                            // Ensure location_id is set to coordinator's location
                            $data['location_id'] = Auth::user()->location_id;
                            
                            $record->update($data);

                            Notification::make()
                                ->title('Winning amount updated successfully')
                                ->success()
                                ->send();

                            $this->refresh();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->refresh();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->refresh();
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Add Winning Amount')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('game_type_id')
                                    ->label('Bet Type')
                                    ->relationship('gameType', 'name')
                                    ->required(),
                                Forms\Components\Hidden::make('location_id')
                                    ->default(Auth::user()->location_id),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Bet Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('winning_amount')
                                    ->label('Winning Amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱')
                                    ->helperText('The amount a player wins when their bet matches the winning number'),
                            ]),
                    ])
                    ->action(function (array $data): void {
                        // Ensure location_id is set to coordinator's location
                        $data['location_id'] = Auth::user()->location_id;
                        
                        // Check if a winning amount with the same game type and location already exists
                        $exists = WinningAmount::where('game_type_id', $data['game_type_id'])
                            ->where('location_id', $data['location_id'])
                            ->where('amount', $data['amount'])
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('A winning amount with these details already exists')
                                ->danger()
                                ->send();

                            return;
                        }

                        WinningAmount::create($data);

                        Notification::make()
                            ->title('Winning amount added successfully')
                            ->success()
                            ->send();

                        $this->refresh();
                    }),
            ]);
    }

    #[On('winning-amount-created')]
    #[On('winning-amount-updated')]
    #[On('winning-amount-deleted')]
    #[On('refresh')]
    public function refresh(): void
    {
        $this->computeWinningAmountStats();
        // Refresh the table by re-rendering the component
        $this->dispatch('$refresh');
    }
}
