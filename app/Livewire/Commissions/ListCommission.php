<?php

namespace App\Livewire\Commissions;

use App\Models\User;
use App\Models\Commission;
use App\Models\Location;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Attributes\On;

class ListCommission extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    /**
     * Statistics for commissions
     *
     * @var array
     */
    public array $commissionStats = [];
    
    /**
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        $this->computeCommissionStats();
    }
    
    /**
     * Compute commission statistics
     *
     * @return void
     */
    public function computeCommissionStats()
    {
        // Get total tellers with commissions
        $totalTellers = Commission::count();
        
        // Get average commission rate
        $avgRate = Commission::avg('rate') ?? 0;
        
        // Get commission stats by location
        $locationStats = Location::select('locations.id', 'locations.name')
            ->selectRaw('COUNT(commissions.id) as teller_count')
            ->selectRaw('AVG(commissions.rate) as avg_rate')
            ->leftJoin('users', 'locations.id', '=', 'users.location_id')
            ->leftJoin('commissions', 'users.id', '=', 'commissions.teller_id')
            ->where('users.role', 'teller')
            ->groupBy('locations.id', 'locations.name')
            ->get()
            ->keyBy('id')
            ->toArray();
        
        $this->commissionStats = [
            'total_tellers' => $totalTellers,
            'avg_rate' => $avgRate,
            'location_stats' => $locationStats,
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
        $this->computeCommissionStats();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Commission::query()->with(['teller', 'teller.location']))
            ->columns([
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller.location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Total Earned')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault : true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault : true)
                  
            ])
            ->filters([
                SelectFilter::make('teller_id')
                    ->label('Teller')
                    ->relationship('teller', 'name', fn (Builder $query) => $query->where('role', 'teller'))
                    ->searchable()
                    ->columnSpan(1)
                    ->preload(),
                SelectFilter::make('location')
                ->columnSpan(1)
                    ->label('Location')
                    ->relationship('teller.location', 'name'),
                // Tables\Filters\Filter::make('rate')
                    
                //     ->form([
                //         \Filament\Forms\Components\Grid::make(12)
                //             ->schema([
                //                 \Filament\Forms\Components\TextInput::make('rate_from')
                //                     ->label('From')
                //                     ->numeric()
                //                     ->columnSpan(6)
                //                     ->suffixIcon('heroicon-o-banknotes'),
                //                 \Filament\Forms\Components\TextInput::make('rate_to')
                //                     ->label('To')
                //                     ->numeric()
                //                     ->columnSpan(6)
                //                     ->suffixIcon('heroicon-o-banknotes'),
                //             ])
                //     ])

                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['rate_from'],
                //                 fn (Builder $query, $rate): Builder => $query->where('rate', '>=', (float) $rate),
                //             )
                //             ->when(
                //                 $data['rate_to'],
                //                 fn (Builder $query, $rate): Builder => $query->where('rate', '<=', (float) $rate),
                //             );
                //     })
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('rate')
                            ->label('Commission Rate (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(fn($record) => $record->rate)
                            ->helperText('Enter the commission rate for this teller.'),
                    ])
                    ->action(function ($record, $data) {
                        $record->rate = $data['rate'];
                        $record->save();
                        
                        Notification::make()
                            ->title('Commission Updated')
                            ->success()
                            ->body('The commission rate for ' . $record->teller->name . ' has been updated to ' . $data['rate'] . '%.')
                            ->send();
                            
                        $this->dispatch('refresh');
                    })
                    ->modalWidth('md')
                    ->modalHeading('Edit Commission Rate')
                    ->modalSubmitAction(fn ($action) => $action->label('Save'))
                    ->modalCancelAction(fn ($action) => $action->label('Cancel'))
                    ->closeModalByClickingAway(true),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        $this->dispatch('refresh');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            $this->dispatch('refresh');
                        }),
                ]),
            ])
            ->groups([
                Group::make('teller.location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Commission $record): string => $record->teller->location?->name ?? 'No Location'),
            ])
            ->defaultGroup('teller.location.name')
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Add Commission')
                    ->icon('heroicon-o-plus')
                    ->form([
                        \Filament\Forms\Components\Select::make('teller_id')
                            ->label('Teller')
                            ->options(User::where('role', 'teller')
                                ->whereDoesntHave('commission')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\TextInput::make('rate')
                            ->label('Commission Rate (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(10)
                            ->helperText('Enter the commission rate for this teller.'),
                    ])
                    ->action(function ($data) {
                        $teller = User::find($data['teller_id']);
                        
                        if (!$teller) {
                            Notification::make()
                                ->title('Error')
                                ->danger()
                                ->body('Teller not found.')
                                ->send();
                            return;
                        }
                        
                        if ($teller->commission) {
                            Notification::make()
                                ->title('Error')
                                ->danger()
                                ->body('This teller already has a commission rate set.')
                                ->send();
                            return;
                        }
                        
                        Commission::create([
                            'teller_id' => $data['teller_id'],
                            'rate' => $data['rate'],
                        ]);
                        
                        Notification::make()
                            ->title('Commission Added')
                            ->success()
                            ->body('Commission rate of ' . $data['rate'] . '% has been set for ' . $teller->name . '.')
                            ->send();
                            
                        $this->dispatch('refresh');
                    })
                    ->modalWidth('md')
                    ->modalHeading('Add New Commission')
                    ->modalSubmitAction(fn ($action) => $action->label('Save'))
                    ->modalCancelAction(fn ($action) => $action->label('Cancel'))
                    ->closeModalByClickingAway(true),
            ]);
    }

    public function render(): View
    {
        return view('livewire.commissions.list-commission');
    }
}
