<?php

namespace App\Livewire\WinningAmount;

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

class ListWinningAmount extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Statistics for winning amounts
     *
     * @var array
     */
    public array $winningAmountStats = [];

    /**
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        $this->computeWinningAmountStats();
    }

    /**
     * Compute winning amount statistics
     *
     * @return void
     */
    public function computeWinningAmountStats()
    {
        // Get total count of winning amount configurations
        $totalConfigs = WinningAmount::count();

        // Get counts by game type
        $gameTypeCounts = WinningAmount::join('game_types', 'winning_amounts.game_type_id', '=', 'game_types.id')
            ->select('game_types.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('game_types.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        // Get counts by location
        $locationCounts = WinningAmount::join('locations', 'winning_amounts.location_id', '=', 'locations.id')
            ->select('locations.name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('locations.name')
            ->get()
            ->keyBy('name')
            ->toArray();

        // Calculate average winning amount
        $avgWinningAmount = WinningAmount::avg('winning_amount');

        $this->winningAmountStats = [
            'total_configs' => $totalConfigs,
            'game_type_counts' => $gameTypeCounts,
            'location_counts' => $locationCounts,
            'avg_winning_amount' => $avgWinningAmount,
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
        // Recompute stats when refreshed
        $this->computeWinningAmountStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(WinningAmount::query()->with(['gameType', 'location']))
            ->groups([
                Group::make('gameType.name')
                    ->label('Game Type')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (WinningAmount $record): string => $record->gameType?->name ?? 'Unknown Game Type'),
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (WinningAmount $record): string => $record->location?->name ?? 'No Location'),
            ])
            ->defaultGroup('gameType.name')
            ->columns([
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->label('Winning Amount')
                    ->money('PHP')
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
                        ->form(function (WinningAmount $record) {
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
                            $record->update($data);

                            Notification::make()
                                ->title('Winning amount updated successfully')
                                ->success()
                                ->send();

                            $this->dispatch('refresh');
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->dispatch('refresh');
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->after(function () {
                            $this->dispatch('refresh');
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
                                    ->label('Game Type')
                                    ->relationship('gameType', 'name')
                                    ->required(),
                                Forms\Components\Select::make('location_id')
                                    ->label('Location')
                                    ->relationship('location', 'name')
                                    ->required(),
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

                        $this->dispatch('refresh');
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.winning-amount.list-winning-amount', [
            'stats' => $this->winningAmountStats,
        ]);
    }
}
