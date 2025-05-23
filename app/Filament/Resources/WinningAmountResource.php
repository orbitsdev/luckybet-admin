<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WinningAmountResource\Pages;
use App\Filament\Resources\WinningAmountResource\RelationManagers;
use App\Models\WinningAmount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WinningAmountResource extends Resource
{
    protected static ?string $model = WinningAmount::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Winning Amounts';
    protected static ?string $navigationGroup = 'Betting Management';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Location'),
                Forms\Components\Select::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->step(0.01)
                    ->label('Bet Amount')
                    ->helperText('The amount placed as a bet'),
                Forms\Components\TextInput::make('winning_amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->step(0.01)
                    ->label('Winning Amount')
                    ->helperText('The amount to be paid out if the bet wins'),
            ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('location.name')
                    ->label('Location')
                    ->collapsible(),
                Tables\Grouping\Group::make('gameType.name')
                    ->label('Game Type')
                    ->collapsible(),
                Tables\Grouping\Group::make('gameType.code')
                    ->label('Game Code')
                    ->collapsible(),
            ])
            ->defaultGroup('location.name')
            ->columns([
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gameType.code')
                    ->label('Game Code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable()
                    ->label('Bet Amount'),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->money('PHP')
                    ->sortable()
                    ->label('Winning Amount'),
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
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListWinningAmounts::route('/'),
            'create' => Pages\CreateWinningAmount::route('/create'),
            'edit' => Pages\EditWinningAmount::route('/{record}/edit'),
        ];
    }
}
