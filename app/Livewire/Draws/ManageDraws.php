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

class ManageDraws extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public ?string $filterDate = null;
    public bool $showAddModal = false;

    // Removed direct draw form state and logic. All create/edit handled by Filament modal actions.



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
                                ->default(fn() => now()->toDateString()) // Default to today dynamically
                                ->live()
                                ->afterStateUpdated(function ($state, $livewire) {
                                    $livewire->filterDate = $state;
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
                    }),
                Action::make('edit')
                    ->icon('heroicon-o-pencil-square')
                    ->label('Edit')
                    ->modalWidth(MaxWidth::SevenExtraLarge)
                    ->modalHeading('Edit Draw')
                    ->modalSubmitActionLabel('Save Changes')
                    ->form(fn(Draw $record) => [
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
            ]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }



    public function getCurrentFilterDateProperty()
    {
        return $this->tableFilters['draw_date']['value'] ?? null;
    }

    public function render(): View
    {
        return view('livewire.draws.manage-draws');
    }
}

/*******  da7bbeeb-ceda-434d-b10e-b3456bd884f6  *******/
