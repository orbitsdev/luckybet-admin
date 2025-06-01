<?php

namespace App\Filament\Resources\BetRatioResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RestrictionsRelationManager extends RelationManager
{
    protected static string $relationship = 'restrictions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->maxLength(4)
                    ->label('Bet Number')
                    ->helperText('The specific number to restrict'),
                Forms\Components\TextInput::make('amount_limit')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->prefix('₱')
                    ->label('Amount Limit')
                    ->helperText('Maximum bet amount allowed for this number'),
                Forms\Components\TimePicker::make('draw_time')
                    ->seconds(true)
                    ->label('Draw Time')
                    ->helperText('Optional: Only apply to this specific draw time')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Bet Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->label('Bet Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount_limit')
                    ->money('PHP')
                    ->label('Amount Limit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->label('Draw Time')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
