<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use Filament\Tables;

use Livewire\Component;


use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Group;
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

    public $draw_date = '';
    public $draw_time = '';
    public $is_open = true;
    public $is_active = true;
    public $drawTimeOptions = [];

    public function mount()
    {
        $this->fetchDrawTimeOptions();
        $this->resetForm();
    }

    public function fetchDrawTimeOptions()
    {
        $schedules = \App\Models\Schedule::where('is_active', true)->get();
        $this->drawTimeOptions = [];
        foreach ($schedules as $schedule) {
            $this->drawTimeOptions[$schedule->draw_time] = $schedule->name . ' (' . $schedule->draw_time . ')';
        }
    }

    protected function rules()
    {
        return [
            'draw_date' => ['required', 'date'],
            'draw_time' => ['required', 'string'],
            'is_open' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function resetForm()
    {
        $this->draw_date = '';
        $this->draw_time = '';
        $this->is_open = true;
        $this->is_active = true;
    }

    public function addDrawAction(): CreateAction
    {
        return CreateAction::make('addDraw')
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
                                        ->options(function() {
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
                    Wizard\Step::make('Bet Ratios')
                        ->icon('heroicon-o-scale')
                        ->schema([
                            Repeater::make('betRatios')
                                ->label('Bet Ratios')
                                ->relationship('betRatios')
                                ->schema([
                                    Grid::make(4)
                                        ->schema([
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
                    Wizard\Step::make('Low Win Numbers')
                        ->icon('heroicon-o-arrow-trending-down')
                        ->schema([
                            Repeater::make('lowWinNumbers')
                                ->label('Low Win Numbers')
                                ->relationship('lowWinNumbers')
                                ->schema([
                                    Grid::make(5)
                                        ->schema([
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
                    Wizard\Step::make('Winning Numbers')
                        ->icon('heroicon-o-trophy')
                        ->schema([
                            Section::make('Add Winning Numbers')
                                ->description('Enter the winning numbers for this draw')
                                ->schema([
                                    Group::make()
                                        ->relationship('result')
                                        ->schema([
                                            // Winning number fields
                                            TextInput::make('s2_winning_number')
                                                ->label('2-Digit (S2)')
                                                ->mask('99')
                                                ->nullable(),
                                            TextInput::make('s3_winning_number')
                                                ->label('3-Digit (S3)')
                                                ->mask('999')
                                                ->nullable(),
                                            TextInput::make('d4_winning_number')
                                                ->label('4-Digit (D4)')
                                                ->mask('9999')
                                                ->nullable(),
                                        ])
                                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $livewire) {
                                            // Get the current Draw record being edited
                                            $draw = $livewire->record;

                                            // Add draw_date and draw_time to the Result data
                                            $data['draw_date'] = $draw->draw_date;
                                            $data['draw_time'] = $draw->draw_time;

                                            return $data;
                                        })
                                        ->columns(3)
                                ])
                        ])->hidden(function (string $operation, $livewire, $get): bool {
                            // Hide when creating a new record
                            if ($operation === 'create') {
                                return true;
                            }
                            
                            // Hide when the draw is still open - use the live toggle value
                            if ($operation === 'edit') {
                                return $get('is_open') === true;
                            }
                            
                            return false;
                        }),
                ])
                ->skippable()->columnSpanFull()
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Draw::query()->with('result'))
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('draw_date')->label('Date'),
                Tables\Columns\TextColumn::make('draw_time')->label('Time'),
                Tables\Columns\TextColumn::make('result.s2_winning_number')->label('S2 Result'),
                Tables\Columns\TextColumn::make('result.s3_winning_number')->label('S3 Result'),
                Tables\Columns\TextColumn::make('result.d4_winning_number')->label('D4 Result'),
            ])
            ->filters(
                [
                    Filter::make('draw_date')
                        ->label('Draw Date')
                        ->form([
                            DatePicker::make('draw_date')->label('Draw Date')
                        ])
                        ->query(fn($query, $data) => $query->when($data['draw_date'] ?? null, fn($q, $date) => $q->where('draw_date', $date))),
                ],
                layout: FiltersLayout::AboveContent
            )

            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->action(fn(Draw $record) => $this->startEdit($record->id)),
            ]);
    }

    public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
    {
        return null;
    }

    public function saveDraw()
    {
        $this->validate();
        Draw::create([
            'draw_date' => $this->draw_date,
            'draw_time' => $this->draw_time,
            'is_open' => $this->is_open,
            'is_active' => $this->is_active,
        ]);
        $this->showAddModal = false;
        $this->resetForm();
        $this->dispatch('$refresh');
    }

    public function startEdit($drawId)
    {
        // Placeholder for edit logic
    }

    public function render(): View
    {
        return view('livewire.draws.manage-draws');
    }
}

/*******  da7bbeeb-ceda-434d-b10e-b3456bd884f6  *******/