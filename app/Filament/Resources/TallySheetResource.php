<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TallySheetResource\Pages;
use App\Filament\Resources\TallySheetResource\RelationManagers;
use App\Models\TallySheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TallySheetResource extends Resource
{
    protected static ?string $model = TallySheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('teller_id')
                    ->tel()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('location_id')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('sheet_date')
                    ->required(),
                Forms\Components\TextInput::make('total_sales')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_claims')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_commission')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('net_amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teller_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sheet_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_claims')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_commission')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_amount')
                    ->numeric()
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
            'index' => Pages\ListTallySheets::route('/'),
            'create' => Pages\CreateTallySheet::route('/create'),
            'edit' => Pages\EditTallySheet::route('/{record}/edit'),
        ];
    }
}
