<?php

namespace App\Livewire\Users;

use App\Models\User;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ListUsers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $userStats = [];

    public function mount()
    {
        $this->userStats = [
            'total' => User::whereIn('role', ['coordinator', 'teller'])->count(),
            'active' => User::whereIn('role', ['coordinator', 'teller'])->where('is_active', true)->count(),
            'inactive' => User::whereIn('role', ['coordinator', 'teller'])->where('is_active', false)->count(),
            'coordinators' => User::where('role', 'coordinator')->count(),
            'tellers' => User::where('role', 'teller')->count(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->headerActions([
                Tables\Actions\Action::make('create')
                    ->label('Create User')
                    ->icon('heroicon-o-plus')
                    ->url(route('manage.users.create')),
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
                Tables\Columns\BadgeColumn::make('role')
                    ->colors([
                        'primary' => 'admin',
                        'success' => 'coordinator',
                        'warning' => 'teller',
                        'info' => 'customer',
                    ]),
                Tables\Columns\TextColumn::make('coordinator.name')
                    ->label('Coordinator')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission.rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(fn($state, $record) => $record->role === 'teller' && $state !== null ? $state . '%' : '-')
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
                Tables\Filters\SelectFilter::make('role')
                    ->label('User Role')
                    ->options([
                        // 'admin' => 'Admin',
                        'coordinator' => 'Coordinator',
                        'teller' => 'Teller',
                        // 'customer' => 'Customer',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Location')
                    ->relationship('location', 'name'),
                ],
                // layout: FiltersLayout::AboveContent
                )
            ->actions([
                Tables\Actions\Action::make('manageCommission')
                        ->label('Commission')
                        ->button()
                        ->icon('heroicon-o-currency-dollar')
                        ->visible(fn($record) => $record->role === 'teller')
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
                            return view('livewire.users.view-user-details', ['record' => $record]);
                        })
                        ->modalWidth('7xl')
                        ->modalHeading('User Details')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn ($action) => $action->label('Close'))
                        ->disabledForm()
                        // ->slideOver()
                        ->closeModalByClickingAway(true),
                    Tables\Actions\Action::make('edit')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil')
                        ->url(fn($record) => route('manage.users.edit', ['user' => $record->id])),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn (Builder $query) => $query->where('role', '!=', 'admin'))
            //group by role and defualt group role 
            ->groups([
                Group::make('role')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (User $record): string => ucfirst($record->role)),
                Group::make('location.name')
                    ->label('Location')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (User $record): string => $record->location?->name ?? 'No Location'),
            ])
            ->defaultGroup('role');
           
    }


    public function render(): View
    {
        return view('livewire.users.list-users');
    }
}
