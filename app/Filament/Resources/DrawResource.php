<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrawResource\Pages;
use App\Filament\Resources\DrawResource\RelationManagers;
use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrawResource extends Resource
{
    protected static ?string $model = Draw::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('draw_date')
                    ->required(),
                Forms\Components\TimePicker::make('draw_time')
                    ->seconds(true)
                    ->required(),
                // Game type field removed as per documentation
                // Each result will have separate fields for different game types
                Forms\Components\Toggle::make('is_open')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                // Game type column removed as per documentation
                Tables\Columns\IconColumn::make('is_open')
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
            'index' => Pages\ListDraws::route('/'),
            'create' => Pages\CreateDraw::route('/create'),
            'edit' => Pages\EditDraw::route('/{record}/edit'),
        ];
    }
}
