<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use Filament\Tables;

use Livewire\Component;


use Filament\Tables\Table;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\DatePicker as D;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Notifications\Notification;

class ManageDraws extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    /**
     * The currently selected filter date
     *
     * @var string|null
     */
    public $filterDate;

    /**
     * Statistics for the currently selected date
     *
     * @var array
     */
    public array $drawStats = [];


    /**
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        // Set default filter date to today
        if (!$this->filterDate) {
            $this->filterDate = now()->format('Y-m-d');
        }
        
        $this->computeDrawStats();
    }
    
    /**
     * Compute draw statistics for the selected date
     *
     * @return void
     */
    public function computeDrawStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Build optimized query with eager loading
        $draws = \App\Models\Draw::query()
            ->where('draw_date', $date)
            ->with([
                'bets' => function($query) {
                    $query->with(['teller:id,name', 'gameType:id,code,name', 'location:id,name']);
                },
                'result'
            ])
            ->get();

        $totalBets = 0;
        $totalHits = 0;
        $totalBetAmount = 0;
        $totalWinAmount = 0;
        
        // Enhanced stats tracking
        $tellerGameTypeStats = [];
        $locationStats = []; // Track stats by location
        $gameTypes = [
            's2' => 'S2',
            's3' => 'S3',
            'd4' => 'D4',
            'd4-s2' => 'D4-S2',
            'd4-s3' => 'D4-S3',
        ];

        foreach ($draws as $draw) {
            foreach ($draw->bets as $bet) {
                $totalBets++;
                $totalBetAmount += $bet->amount;
                
                $teller = $bet->teller?->name ?? 'Unknown';
                $tellerId = $bet->teller?->id ?? 0;
                $locationName = $bet->location?->name ?? 'Unknown';
                $locationId = $bet->location?->id ?? 0;
                $gameType = $bet->gameType?->code ?? 'Unknown';
                $subSelection = $bet->d4_sub_selection ?? '';
                
                // Handle D4 sub-selections
                if ($gameType === 'D4' && !empty($subSelection)) {
                    $gameType = "D4-{$subSelection}";
                }
                
                // Initialize location stats if not exists
                if (!isset($locationStats[$locationId])) {
                    $locationStats[$locationId] = [
                        'name' => $locationName,
                        'total_hits' => 0,
                        'game_types' => array_fill_keys(array_keys($gameTypes), 0),
                        'tellers' => [],
                    ];
                }
                
                // Initialize teller stats if not exists
                if (!isset($locationStats[$locationId]['tellers'][$tellerId])) {
                    $locationStats[$locationId]['tellers'][$tellerId] = [
                        'name' => $teller,
                        'total' => 0,
                        'total_hits' => 0,
                        'game_types' => array_fill_keys(array_keys($gameTypes), 0),
                    ];
                }
                
                // Also maintain the flat teller stats for backward compatibility
                if (!isset($tellerGameTypeStats[$teller])) {
                    $tellerGameTypeStats[$teller] = [
                        'location' => $locationName,
                        'total' => 0,
                        'total_hits' => 0,
                        'game_types' => array_fill_keys(array_keys($gameTypes), 0),
                    ];
                }
                
                // Count total bets by teller
                $locationStats[$locationId]['tellers'][$tellerId]['total']++;
                $tellerGameTypeStats[$teller]['total']++;
                
                // Check if this bet is a hit (only if draw has results)
                $isHit = false;
                if ($draw->result) {
                    $gameTypeCode = $bet->gameType ? $bet->gameType->code : null;
                    $result = $draw->result;
                    
                    // Optimized hit detection logic
                    switch ($gameTypeCode) {
                        case 'S2':
                            $isHit = $bet->bet_number === $result->s2_winning_number;
                            break;
                            
                        case 'S3':
                            $isHit = $bet->bet_number === $result->s3_winning_number;
                            break;
                            
                        case 'D4':
                            // First check exact D4 match
                            if ($bet->bet_number === $result->d4_winning_number) {
                                $isHit = true;
                                break;
                            }
                            
                            // Then check D4 sub-selections if applicable
                            if ($bet->d4_sub_selection && $result->d4_winning_number) {
                                $sub = strtoupper($bet->d4_sub_selection);
                                if ($sub === 'S2') {
                                    $isHit = substr($result->d4_winning_number, -2) === str_pad($bet->bet_number, 2, '0', STR_PAD_LEFT);
                                } elseif ($sub === 'S3') {
                                    $isHit = substr($result->d4_winning_number, -3) === str_pad($bet->bet_number, 3, '0', STR_PAD_LEFT);
                                }
                            }
                            break;
                    }
                    
                    if ($isHit) {
                        $totalHits++;
                        $totalWinAmount += $bet->winning_amount;
                        
                        // Convert gameType to lowercase for consistent array keys
                        $gameTypeKey = strtolower($gameType);
                        
                        // Count hits by location
                        $locationStats[$locationId]['total_hits']++;
                        if (isset($locationStats[$locationId]['game_types'][$gameTypeKey])) {
                            $locationStats[$locationId]['game_types'][$gameTypeKey]++;
                        }
                        
                        // Count hits by teller within location
                        $locationStats[$locationId]['tellers'][$tellerId]['total_hits']++;
                        if (isset($locationStats[$locationId]['tellers'][$tellerId]['game_types'][$gameTypeKey])) {
                            $locationStats[$locationId]['tellers'][$tellerId]['game_types'][$gameTypeKey]++;
                        }
                        
                        // Count hits in flat teller array (for backward compatibility)
                        $tellerGameTypeStats[$teller]['total_hits']++;
                        if (isset($tellerGameTypeStats[$teller]['game_types'][$gameTypeKey])) {
                            $tellerGameTypeStats[$teller]['game_types'][$gameTypeKey]++;
                        }
                    }
                }
            }
        }

        $this->drawStats = [
            'total_bets' => $totalBets,
            'total_hits' => $totalHits,
            'total_bet_amount' => $totalBetAmount,
            'total_win_amount' => $totalWinAmount,
            'teller_game_stats' => $tellerGameTypeStats,
            'location_stats' => $locationStats,
            'game_types' => $gameTypes,
        ];
    }




    public function table(Table $table): Table
    {
        return $table
            ->query(Draw::query()->with('result'))
            ->headerActions([
                CreateAction::make('addDraw')
                    ->label('Add Draw')
                    ->button()
                    ->model(Draw::class)
                    ->createAnother(false)
                    ->form([
                        Wizard::make([
                            Wizard\Step::make('Draw Information')
                                ->icon('heroicon-o-calendar')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            D::make('draw_date')
                                                ->required()
                                                ->default(now()),
                                            Select::make('draw_time')
                                                ->label('Draw Time')
                                                ->options(function () {
                                                    $schedules = \App\Models\Schedule::where('is_active', true)->get();
                                                    $options = [];
                                                    foreach ($schedules as $schedule) {
                                                        $options[$schedule->draw_time] = "{$schedule->name} ({$schedule->draw_time})";
                                                    }
                                                    return $options;
                                                })
                                                ->searchable()
                                                ->required(),
                                        ]),
                                    Toggle::make('is_open')
                                        ->required()->default(true)
                                        ->live()
                                        ->helperText('Note: Winning numbers can only be entered after the draw is closed.'),
                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->default(true)
                                        ->helperText('Hide this draw from dropdowns and betting screens without deleting.'),
                                ]),
                        ])
                            ->skippable()->columnSpanFull()

                    ])
            ])
            ->columns([
                // Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('draw_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('result.s2_winning_number')
                    ->label('S2 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('result.s3_winning_number')
                    ->label('S3 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('result.d4_winning_number')
                    ->label('D4 Winner')
                    ->placeholder('-')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\ToggleColumn::make('is_open')
                    ->label('Open')
                    ->sortable()
                    ->tooltip('Toggle whether this draw is currently open for accepting bets.'),
            ])
            ->filters(
                [
                    Filter::make('draw_date')
                        ->label('Draw Date')
                        ->form([
                            DatePicker::make('draw_date')
                                ->label('Draw Date')
                                ->nullable() // Allow clearing the filter
                                ->default(fn() => now()->toDateString()) // Default to today dynamically
                                ->live()
                                ->afterStateUpdated(function ($state, $livewire) {
                                    $livewire->filterDate = $state;
                                    $livewire->computeDrawStats();
                                })

                        ])
                        ->query(fn($query, $data) => $query->when($data['draw_date'] ?? null, fn($q, $date) => $q->where('draw_date', $date))),
                ],
                layout: FiltersLayout::AboveContent
            )

            ->actions([
                Action::make('manageResult')
                    ->button()
                    ->label('Manage Result')
                    ->icon('heroicon-o-trophy')
                    ->visible(fn(Draw $record) => !$record->is_open)
                    ->modalHeading('Manage Winning Numbers')
                    ->modalSubmitActionLabel('Save Result')
                    ->form(fn(Draw $record) => [
                        Section::make('Add Winning Numbers')
                            ->description('Enter the winning numbers for this draw')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('s2_winning_number')
                                            ->label('2-Digit (S2)')
                                            ->mask('99')
                                            ->nullable()
                                            ->default(optional($record->result)->s2_winning_number),
                                        TextInput::make('s3_winning_number')
                                            ->label('3-Digit (S3)')
                                            ->mask('999')
                                            ->nullable()
                                            ->default(optional($record->result)->s3_winning_number),
                                        TextInput::make('d4_winning_number')
                                            ->label('4-Digit (D4)')
                                            ->mask('9999')
                                            ->nullable()
                                            ->default(optional($record->result)->d4_winning_number),
                                    ])
                                    ->columns(3)
                            ])
                    ])
                    ->action(function (Draw $record, array $data) {
                        $record->result()->updateOrCreate([], [
                            's2_winning_number' => $data['s2_winning_number'] ?? null,
                            's3_winning_number' => $data['s3_winning_number'] ?? null,
                            'd4_winning_number' => $data['d4_winning_number'] ?? null,
                            'draw_date' => $record->draw_date,
                            'draw_time' => $record->draw_time,
                        ]);
                        Notification::make()
                            ->title('Result Updated')
                            ->success()
                            ->body('Draw for ' . ($record->draw_date ?? 'Unknown Date') . ' at ' . ($record->draw_time ?? 'Unknown Time') . ' has been updated.')
                            ->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Action::make('edit')
                        ->icon('heroicon-o-pencil-square')
                        ->label('Edit')
                        ->modalWidth(MaxWidth::SevenExtraLarge)
                        ->modalHeading('Edit Draw')
                        ->modalSubmitActionLabel('Save Changes')
                        ->form(fn(Draw $record) => [
                            Section::make('Draw Details')
                                ->schema([
                                    Tabs::make('Edit Draw')->tabs([
                                        Tabs\Tab::make('Draw Information')
                                            ->schema([
                                                Grid::make(2)->schema([
                                                    DatePicker::make('draw_date')
                                                        ->label('Draw Date')
                                                        ->required()
                                                        ->default($record->draw_date),
                                                    Select::make('draw_time')
                                                        ->label('Draw Time')
                                                        ->options(function() {
                                                            $schedules = \App\Models\Schedule::where('is_active', true)->get();
                                                            $options = [];
                                                            foreach ($schedules as $schedule) {
                                                                $options[$schedule->draw_time] = $schedule->name . ' (' . $schedule->draw_time . ')';
                                                            }
                                                            return $options;
                                                        })
                                                        ->searchable()
                                                        ->required(),
                                                ]),
                                                Toggle::make('is_open')
                                                    ->required()
                                                    ->default($record->is_open)
                                                    ->live()
                                                    ->helperText('Note: Winning numbers can only be entered after the draw is closed.'),
                                                Toggle::make('is_active')
                                                    ->label('Active')
                                                    ->default($record->is_active)
                                                    ->helperText('Hide this draw from dropdowns and betting screens without deleting.'),
                                            ]),
                                        Tabs\Tab::make('Bet Ratios')
                                            ->schema([
                                                Repeater::make('betRatios')
                                                    ->label('Bet Ratios')
                                                    ->relationship('betRatios')
                                                    ->schema([
                                                        Grid::make(4)->schema([
                                                            Select::make('location_id')
                                                                ->label('Location')
                                                                ->relationship('location', 'name')
                                                                ->required(),
                                                            Select::make('game_type_id')
                                                                ->label('Game Type')
                                                                ->relationship('gameType', 'name')
                                                                ->required(),
                                                            TextInput::make('bet_number')
                                                                ->label('Bet Number')
                                                                ->required(),
                                                            TextInput::make('max_amount')
                                                                ->label('Max Amount')
                                                                ->numeric()
                                                                ->required(),
                                                        ])
                                                    ])
                                                    ->defaultItems(0)
                                                    ->columnSpanFull()
                                            ]),
                                        Tabs\Tab::make('Low Win Numbers')
                                            ->schema([
                                                Repeater::make('lowWinNumbers')
                                                    ->label('Low Win Numbers')
                                                    ->relationship('lowWinNumbers')
                                                    ->schema([
                                                        Grid::make(5)->schema([
                                                            Select::make('location_id')
                                                                ->label('Location')
                                                                ->relationship('location', 'name')
                                                                ->required(),
                                                            Select::make('game_type_id')
                                                                ->label('Game Type')
                                                                ->relationship('gameType', 'name')
                                                                ->required(),
                                                            TextInput::make('bet_number')
                                                                ->label('Bet Number')
                                                                ->required(),
                                                            TextInput::make('winning_amount')
                                                                ->label('Override Amount')
                                                                ->numeric()
                                                                ->required(),
                                                            TextInput::make('reason')
                                                                ->label('Reason')
                                                                ->nullable(),
                                                        ])
                                                    ])
                                                    ->defaultItems(0)
                                                    ->columnSpanFull()
                                            ]),
                                        Tabs\Tab::make('Winning Numbers')
                                            ->hidden(fn ($get) => $get('is_open') === true)
                                            ->schema([
                                                Section::make('Add Winning Numbers')
                                                    ->description('Enter the winning numbers for this draw')
                                                    ->schema([
                                                        Group::make()
                                                            ->schema([
                                                                TextInput::make('s2_winning_number')
                                                                    ->label('2-Digit (S2)')
                                                                    ->mask('99')
                                                                    ->nullable()
                                                                    ->default(optional($record->result)->s2_winning_number),
                                                                TextInput::make('s3_winning_number')
                                                                    ->label('3-Digit (S3)')
                                                                    ->mask('999')
                                                                    ->nullable()
                                                                    ->default(optional($record->result)->s3_winning_number),
                                                                TextInput::make('d4_winning_number')
                                                                    ->label('4-Digit (D4)')
                                                                    ->mask('9999')
                                                                    ->nullable()
                                                                    ->default(optional($record->result)->d4_winning_number),
                                                            ])
                                                            ->columns(3)
                                                    ])
                                            ])
                                    ])
                                ])
                        ])
                        ->action(function (Draw $record, array $data) {
                            // Update the draw record
                            $record->update([
                                'draw_date' => $data['draw_date'],
                                'draw_time' => $data['draw_time'],
                                'is_open' => $data['is_open'],
                                'is_active' => $data['is_active'],
                            ]);
                            // Save betRatios and lowWinNumbers if present
                            if (isset($data['betRatios'])) {
                                $record->betRatios()->delete();
                                foreach ($data['betRatios'] as $ratio) {
                                    $record->betRatios()->create($ratio);
                                }
                            }
                            if (isset($data['lowWinNumbers'])) {
                                $record->lowWinNumbers()->delete();
                                foreach ($data['lowWinNumbers'] as $low) {
                                    $record->lowWinNumbers()->create($low);
                                }
                            }
                            // Save winning numbers if present and draw is closed
                            if (!$data['is_open'] && isset($data['s2_winning_number']) || isset($data['s3_winning_number']) || isset($data['d4_winning_number'])) {
                                $record->result()->updateOrCreate([], [
                                    's2_winning_number' => $data['s2_winning_number'] ?? null,
                                    's3_winning_number' => $data['s3_winning_number'] ?? null,
                                    'd4_winning_number' => $data['d4_winning_number'] ?? null,
                                    'draw_date' => $data['draw_date'],
                                    'draw_time' => $data['draw_time'],
                                ]);
                            }
                        }),
                    DeleteAction::make()
                    ])
            ]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public function render(): View
    {
        return view('livewire.draws.manage-draws');
    }
}

/*******  da7bbeeb-ceda-434d-b10e-b3456bd884f6  *******/
