<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClaimResource\Pages;
use App\Filament\Resources\ClaimResource\RelationManagers;
use App\Models\Claim;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClaimResource extends Resource
{
    protected static ?string $model = Claim::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('bet_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('result_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('teller_id')
                    ->tel()
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('commission_amount')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\DateTimePicker::make('claimed_at'),
                Forms\Components\TextInput::make('qr_code_data')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bet_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('result_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('claimed_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qr_code_data')
                    ->searchable(),
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
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
            'edit' => Pages\EditClaim::route('/{record}/edit'),
        ];
    }
}
