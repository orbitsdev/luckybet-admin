<?php

namespace App\Livewire\Coordinator;

use App\Models\User;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ManageTellers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $tellerStats = [];

    public function mount()
    {
        $coordinatorId = Auth::id();
        
        $this->tellerStats = [
            'total' => User::where('role', 'teller')->where('coordinator_id', $coordinatorId)->count(),
            'active' => User::where('role', 'teller')->where('coordinator_id', $coordinatorId)->where('is_active', true)->count(),
            'inactive' => User::where('role', 'teller')->where('coordinator_id', $coordinatorId)->where('is_active', false)->count(),
        ];
    }

    public function table(Table $table): Table
    {
        $coordinatorId = Auth::id();
        $coordinator = Auth::user();
        
        return $table
            ->query(
                User::query()
                    ->where('role', 'teller')
                    ->where('coordinator_id', $coordinatorId)
            )
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Teller')
                    ->icon('heroicon-o-plus')
                    ->url(route('coordinator.tellers.create')),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(fn($record) => $record->profile_photo_url),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission.rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(fn($state) => $state !== null ? $state . '%' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('manageCommission')
                    ->label('Commission')
                    ->button()
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('commission_rate')
                            ->label('Commission Rate (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(fn($record) => $record->commission->rate ?? null)
                            ->helperText('Enter the commission rate for this teller.'),
                    ])
                    ->action(function ($record, $data) {
                        if ($record->commission) {
                            $record->commission->rate = $data['commission_rate'];
                            $record->commission->save();
                            Notification::make()
                                ->title('Commission Updated')
                                ->success()
                                ->body('The commission rate for ' . $record->name . ' has been updated to ' . $data['commission_rate'] . '%.')
                                ->send();
                        }
                    })
                    ->modalWidth('md')
                    ->modalHeading('Manage Commission')
                    ->modalSubmitAction(fn ($action) => $action->label('Save'))
                    ->modalCancelAction(fn ($action) => $action->label('Cancel'))
                    ->closeModalByClickingAway(true),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->color('success')
                        ->icon('heroicon-m-eye')
                        ->label('View')
                        ->modalContent(function ($record) {
                            return view('livewire.coordinator.view-teller-details', ['record' => $record]);
                        })
                        ->modalWidth('7xl')
                        ->modalHeading('Teller Details')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn ($action) => $action->label('Close'))
                        ->disabledForm()
                        ->closeModalByClickingAway(true),
                    Tables\Actions\Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->url(fn($record) => route('coordinator.tellers.edit', ['record' => $record->id])),
                    Tables\Actions\DeleteAction::make()
                        ->action(function ($record) {
                            $record->delete();
                            Notification::make()
                                ->title('Teller Deleted')
                                ->success()
                                ->body('The teller has been deleted successfully.')
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.coordinator.manage-tellers');
    }
}
