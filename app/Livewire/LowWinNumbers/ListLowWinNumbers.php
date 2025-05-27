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
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
        $this->computeLowWinStats();
    }

    /**
     * Compute low win number statistics
     *
     * @return void
     */
    public function computeLowWinStats()
    {
        $date = $this->filterDate ?: now()->format('Y-m-d');
        
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
                    ->label('Game Type')
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
                SelectFilter::make('game_type_id')
                    ->label('Game Type')
                    ->relationship('gameType', 'name'),
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
                    ->relationship('location', 'name'),
               
            ],
            // layout: FiltersLayout::AboveContent
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
                                        ->label('Game Type')
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
                Tables\Actions\Action::make('add_low_win')
                    ->label('Add Low Win Number')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('draw_id')
                            ->label('Draw')
                            ->options(function () {
                                return Draw::whereDate('draw_date', $this->filterDate ?: now()->format('Y-m-d'))
                                    ->get()
                                    ->mapWithKeys(function ($draw) {
                                        return [$draw->id => $draw->draw_date . ' - ' . $draw->draw_time];
                                    });
                            })
                            ->required()
                            ->searchable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('game_type_id')
                                    ->label('Game Type')
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
                                    ->relationship('location', 'name')
                                    ->required()
                                    ->searchable(),
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
