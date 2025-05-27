<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use App\Models\Schedule;
use Livewire\Component;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class CreateDrawModal extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public $date;

    public function mount($date = null)
    {
        $this->data = [
            'draw_date' => $date ?? now()->toDateString(),
        ];
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                DatePicker::make('draw_date')->required(),
                Select::make('draw_time')->label('Draw Time')->options(
                    Schedule::where('is_active', true)->pluck('draw_time', 'draw_time')
                )->required(),
            ])
            ->statePath('data');
    }

    public function create()
    {
        $this->validate();
        Draw::create([
            'draw_date' => $this->data['draw_date'],
            'draw_time' => $this->data['draw_time'],
            'is_open' => true,
            'is_active' => true,
        ]);
        $this->dispatch('drawCreated');
    }

    public function render()
    {
        return view('livewire.draws.create-draw-modal');
    }
}
