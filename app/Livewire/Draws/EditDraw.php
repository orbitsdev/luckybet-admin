<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Contracts\View\View;

class EditDraw extends Component implements Forms\Contracts\HasForms
{
    use InteractsWithForms;

    public Draw $draw;
    public $draw_date;
    public $draw_time;
    public $is_open;
    public $is_active;
    public $drawTimeOptions = [];
    public $betRatios = [];
    public $lowWinNumbers = [];
    public $winningNumbers = [
        's2_winning_number' => null,
        's3_winning_number' => null,
        'd4_winning_number' => null,
    ];

    public function mount($draw)
    {
        $this->draw = Draw::findOrFail($draw);
        $this->draw_date = $this->draw->draw_date;
        $this->draw_time = $this->draw->draw_time;
        $this->is_open = $this->draw->is_open;
        $this->is_active = $this->draw->is_active;
        $this->fetchDrawTimeOptions();
        // Load related data if needed
        // $this->betRatios = $this->draw->betRatios()->get()->toArray();
        // $this->lowWinNumbers = $this->draw->lowWinNumbers()->get()->toArray();
        // $this->winningNumbers = $this->draw->result ? [
        //     's2_winning_number' => $this->draw->result->s2_winning_number,
        //     's3_winning_number' => $this->draw->result->s3_winning_number,
        //     'd4_winning_number' => $this->draw->result->d4_winning_number,
        // ] : $this->winningNumbers;
    }

    public function fetchDrawTimeOptions()
    {
        $schedules = \App\Models\Schedule::where('is_active', true)->get();
        $this->drawTimeOptions = [];
        foreach ($schedules as $schedule) {
            $this->drawTimeOptions[$schedule->draw_time] = $schedule->name . ' (' . $schedule->draw_time . ')';
        }
    }

    protected function getFormSchema(): array
    {
        $tabs = [
            Tabs\Tab::make('Draw Information')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('draw_date')
                            ->label('Draw Date')
                            ->required()
                            ->default($this->draw_date),
                        Select::make('draw_time')
                            ->label('Draw Time')
                            ->options($this->drawTimeOptions)
                            ->searchable()
                            ->required(),
                    ]),
                    Toggle::make('is_open')
                        ->required()->default($this->is_open)
                        ->live()
                        ->helperText('Note: Winning numbers can only be entered after the draw is closed.'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default($this->is_active)
                        ->helperText('Hide this draw from dropdowns and betting screens without deleting.'),
                ]),
            Tabs\Tab::make('Bet Ratios')
                ->schema([
                    Repeater::make('betRatios')
                        ->label('Bet Ratios')
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
        ];
        // Only show Winning Numbers tab if draw is closed
        if (!$this->is_open) {
            $tabs[] = Tabs\Tab::make('Winning Numbers')
                ->schema([
                    Section::make('Add Winning Numbers')
                        ->description('Enter the winning numbers for this draw')
                        ->schema([
                            Group::make()
                                ->schema([
                                    TextInput::make('winningNumbers.s2_winning_number')
                                        ->label('2-Digit (S2)')
                                        ->mask('99')
                                        ->nullable(),
                                    TextInput::make('winningNumbers.s3_winning_number')
                                        ->label('3-Digit (S3)')
                                        ->mask('999')
                                        ->nullable(),
                                    TextInput::make('winningNumbers.d4_winning_number')
                                        ->label('4-Digit (D4)')
                                        ->mask('9999')
                                        ->nullable(),
                                ])
                                ->columns(3)
                        ])
                ]);
        }
        return [
            Tabs::make('Edit Draw')->tabs($tabs)
        ];
    }

    public function submit()
    {
        $this->validate([
            'draw_date' => ['required', 'date'],
            'draw_time' => ['required', 'string'],
            'is_open' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
        // Save logic for parent draw
        $this->draw->update([
            'draw_date' => $this->draw_date,
            'draw_time' => $this->draw_time,
            'is_open' => $this->is_open,
            'is_active' => $this->is_active,
        ]);
        // TODO: Save betRatios, lowWinNumbers, winningNumbers as needed
        session()->flash('success', 'Draw updated successfully!');
        return redirect()->route('manage.draws');
    }

    public function render(): View
    {
        return view('livewire.draws.edit-draw');
    }
}
