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

class CreateDraw extends Component implements Forms\Contracts\HasForms
{
    use InteractsWithForms;

    public $draw_date = '';
    public $draw_time = '';
    public $is_open = true;
    public $is_active = true;
    public $drawTimeOptions = [];
    public $betRatios = [];
    public $lowWinNumbers = [];
    public $winningNumbers = [
        's2_winning_number' => null,
        's3_winning_number' => null,
        'd4_winning_number' => null,
    ];

    public function mount()
    {
        $this->fetchDrawTimeOptions();
        $this->draw_date = now()->toDateString();
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
        return [
            Tabs::make('Create Draw')
                ->tabs([
                    Tabs\Tab::make('Draw Information')
                        ->schema([
                            Grid::make(2)->schema([
                                DatePicker::make('draw_date')
                                    ->label('Draw Date')
                                    ->required()
                                    ->default(now()),
                                Select::make('draw_time')
                                    ->label('Draw Time')
                                    ->options($this->drawTimeOptions)
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
                        ])
                ])
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
        // Save logic here (add betRatios, lowWinNumbers, winningNumbers as needed)
        Draw::create([
            'draw_date' => $this->draw_date,
            'draw_time' => $this->draw_time,
            'is_open' => $this->is_open,
            'is_active' => $this->is_active,
            // Add relationships as needed
        ]);
        session()->flash('success', 'Draw created successfully!');
        return redirect()->route('manage.draws');
    }

    public function render(): View
    {
        return view('livewire.draws.create-draw');
    }
}
