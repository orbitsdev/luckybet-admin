<?php

namespace App\Livewire\Draws;

use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class EditDraw extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ?Draw $record = null;

    public function mount(Draw $draw): void
    {
        // Set the record property
        $this->record = $draw;

        // Load relationships before filling the form
        $this->record->load(['betRatios', 'lowWinNumbers', 'result']);

        // Prepare data for the form
        $formData = $this->record->attributesToArray();

        // Add bet ratios data
        $formData['betRatios'] = $this->record->betRatios->toArray();

        // Add low win numbers data
        $formData['lowWinNumbers'] = $this->record->lowWinNumbers->toArray();

        // Add result data if available
        if ($this->record->result) {
            $formData['s2_winning_number'] = $this->record->result->s2_winning_number;
            $formData['s3_winning_number'] = $this->record->result->s3_winning_number;
            $formData['d4_winning_number'] = $this->record->result->d4_winning_number;
        }

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Draw Details')
                    ->schema([
                        Tabs::make('Edit Draw')->tabs([
                            Tabs\Tab::make('Draw Information')
                                ->icon('heroicon-o-information-circle')
                                ->schema([
                                    Grid::make(2)->schema([
                                        DatePicker::make('draw_date')
                                            ->label('Draw Date')
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Draw date cannot be modified after creation'),
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
                                            ->required()
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Draw time cannot be modified after creation'),
                                    ]),
                                    Toggle::make('is_open')
                                        ->required()
                                        ->live()
                                        ->helperText('Note: Winning numbers can only be entered after the draw is closed.'),
                                    Toggle::make('is_active')
                                        ->label('Active')
                                        ->helperText('Hide this draw from dropdowns and betting screens without deleting.'),
                                ]),
                            Tabs\Tab::make('Bet Ratios')
                                ->icon('heroicon-o-currency-dollar')
                                ->schema([
                                    Repeater::make('betRatios')
                                        ->label('Bet Ratios')
                                        ->relationship('betRatios')
                                        ->schema([
                                            Grid::make(5)->schema([
                                                Select::make('location_id')
                                                    ->label('Location')
                                                    ->relationship('location', 'name')
                                                    ->required(),
                                                Select::make('game_type_id')
                                                    ->label('Bet Type')
                                                    ->relationship('gameType', 'name')
                                                    ->required()
                                                    ->live(),
                                                TextInput::make('bet_number')
                                                    ->label('Bet Number')
                                                    ->required(),
                                                Select::make('sub_selection')
                                                    ->label('Subtype (for D4 only)')
                                                    ->options([
                                                        'S2' => 'D4-S2 (Last 2 digits)',
                                                        'S3' => 'D4-S3 (Last 3 digits)',
                                                    ])
                                                    ->placeholder('None (Regular number)')
                                                    ->helperText('For D4 numbers, you can specify a subtype')
                                                    ->visible(fn (callable $get) => \App\Models\GameType::find($get('game_type_id'))?->code === 'D4')
                                                    ->nullable(),
                                                TextInput::make('max_amount')
                                                    ->label('Max Amount')
                                                    ->helperText('Set to 0 to mark as sold out')
                                                    ->numeric()
                                                    ->required(),
                                            ])
                                        ])
                                        ->defaultItems(0)
                                        ->columnSpanFull()
                                ]),
                            Tabs\Tab::make('Low Win Numbers')
                                ->icon('heroicon-o-arrow-trending-down')
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
                                                    ->label('Bet Type')
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
                                ->icon('heroicon-o-trophy')
                                ->hidden(function (callable $get) {
                                    return $get('is_open') === true;
                                })
                                ->schema([
                                    Grid::make(3)->schema([
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
                                ])
                        ])
                    ])
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            // Update the draw record
            $this->record->update([
                'is_open' => $data['is_open'],
                'is_active' => $data['is_active'],
            ]);

            // Save betRatios if present
            if (isset($data['betRatios'])) {
                // Get existing bet ratio IDs to determine which ones to keep
                $existingRatioIds = $this->record->betRatios->pluck('id')->toArray();
                $updatedRatioIds = [];

                foreach ($data['betRatios'] as $ratio) {
                    if (isset($ratio['id'])) {
                        // Update existing ratio
                        $betRatio = \App\Models\BetRatio::find($ratio['id']);
                        if ($betRatio) {
                            $betRatio->update($ratio);
                            $updatedRatioIds[] = $betRatio->id;
                        }
                    } else {
                        // Create new ratio
                        $newRatio = $this->record->betRatios()->create($ratio);
                        $updatedRatioIds[] = $newRatio->id;
                    }
                }

                // Delete ratios that weren't updated or created
                $ratiosToDelete = array_diff($existingRatioIds, $updatedRatioIds);
                if (!empty($ratiosToDelete)) {
                    \App\Models\BetRatio::whereIn('id', $ratiosToDelete)->delete();
                }
            }

            // Save lowWinNumbers if present
            if (isset($data['lowWinNumbers'])) {
                // Get existing low win number IDs to determine which ones to keep
                $existingLowWinIds = $this->record->lowWinNumbers->pluck('id')->toArray();
                $updatedLowWinIds = [];

                foreach ($data['lowWinNumbers'] as $low) {
                    if (isset($low['id'])) {
                        // Update existing low win number
                        $lowWin = \App\Models\LowWinNumber::find($low['id']);
                        if ($lowWin) {
                            $lowWin->update($low);
                            $updatedLowWinIds[] = $lowWin->id;
                        }
                    } else {
                        // Create new low win number
                        $newLowWin = $this->record->lowWinNumbers()->create($low);
                        $updatedLowWinIds[] = $newLowWin->id;
                    }
                }

                // Delete low win numbers that weren't updated or created
                $lowWinsToDelete = array_diff($existingLowWinIds, $updatedLowWinIds);
                if (!empty($lowWinsToDelete)) {
                    \App\Models\LowWinNumber::whereIn('id', $lowWinsToDelete)->delete();
                }
            }

            // Save winning numbers if present and draw is closed
            if (!$data['is_open'] && (isset($data['s2_winning_number']) || isset($data['s3_winning_number']) || isset($data['d4_winning_number']))) {
                $this->record->result()->updateOrCreate([], [
                    's2_winning_number' => $data['s2_winning_number'] ?? null,
                    's3_winning_number' => $data['s3_winning_number'] ?? null,
                    'd4_winning_number' => $data['d4_winning_number'] ?? null,
                    'draw_date' => $this->record->draw_date,
                    'draw_time' => $this->record->draw_time,
                ]);
            }

            // Refresh the record to get the latest data
            $this->record->refresh();

            // Add a success notification
            Notification::make()
                ->success()
                ->title('Draw Updated Successfully')
                ->body('Draw for ' . $this->record->draw_date->format('M d, Y') . ' at ' . $this->record->draw_time . ' has been updated.')
                ->send();

            // Redirect back to the manage draws page
            redirect()->route('manage.draws');

        } catch (\Exception $e) {
            // Handle any errors
            Notification::make()
                ->danger()
                ->title('Error Updating Draw')
                ->body('An error occurred: ' . $e->getMessage())
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.draws.edit-draw');
    }
}
