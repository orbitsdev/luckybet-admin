<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameTypeResource\Pages;
use App\Models\GameType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameTypeResource extends Resource
{
    protected static ?string $model = GameType::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Game Types';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(5)
                    ->helperText('Short code for the game type (e.g., S2, S3, D4)'),
                Forms\Components\TextInput::make('digit_count')
                    ->required()
                    ->numeric()
                    ->default(2)
                    ->minValue(1)
                    ->maxValue(10)
                    ->helperText('Number of digits required for this game type (e.g., 2 for S2, 3 for S3, 4 for D4)'),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('digit_count')
                    ->label('Digits')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
            'index' => Pages\ListGameTypes::route('/'),
            'create' => Pages\CreateGameType::route('/create'),
            'edit' => Pages\EditGameType::route('/{record}/edit'),
        ];
    }
}
