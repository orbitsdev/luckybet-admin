<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Exists;
use Mockery\Matcher\Not;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('draw_time')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw_time'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                // Tables\Columns\IconColumn::make('is_open')
                //     ->boolean(),
                Tables\Columns\ToggleColumn::make('is_open')
                ->beforeStateUpdated(function ($record, $state) {

                    $exist = Schedule::where('is_open', true)->where('id', '!=', $record->id)->exists();

                    if ($exist) {
                            Notification::make()
                            ->title('Error: Only one schedule can be open at a time')
                            ->danger()
                            ->send();
                        return ;
                    }

                })
                ->updateStateUsing(function ($record, $state) {
                    if ($state) {
                        $hasOpen = Schedule::where('is_open', true)->where('id', '!=', $record->id)->exists();

                        if ($hasOpen) {
                            Notification::make()
                                ->title('Error: Only one schedule can be open at a time')
                                ->body('Please close the currently open schedule first.')
                                ->danger()
                                ->send();

                            return false;
                        }

                        Schedule::where('is_open', true)->update(['is_open' => false]);
                        $record->update(['is_open' => true]);

                        Notification::make()
                            ->title('Schedule Opened')
                            ->body('This schedule has been opened successfully.')
                            ->success()
                            ->send();
                    } else {
                        $record->update(['is_open' => false]);

                        Notification::make()
                            ->title('Schedule Closed')
                            ->body('This schedule has been closed successfully.')
                            ->success()
                            ->send();
                    }

                    return true;
                })
                ,

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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
