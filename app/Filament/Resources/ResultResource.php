<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('draw_id')
                    ->relationship('draw', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('draw_date')
                    ->required(),
                Forms\Components\TimePicker::make('draw_time')
                    ->required(),
                Forms\Components\TextInput::make('s2_winning_number')
                    ->label('S2 Winning Number (2-digit)')
                    ->required()
                    ->maxLength(2)
                    ->regex('/^\d{2}$/'),
                Forms\Components\TextInput::make('s3_winning_number')
                    ->label('S3 Winning Number (3-digit)')
                    ->required()
                    ->maxLength(3)
                    ->regex('/^\d{3}$/'),
                Forms\Components\TextInput::make('d4_winning_number')
                    ->label('D4 Winning Number (4-digit)')
                    ->required()
                    ->maxLength(4)
                    ->regex('/^\d{4}$/'),
                Forms\Components\Select::make('coordinator_id')
                    ->relationship('coordinator', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw.id')
                    ->numeric()
                    ->sortable()
                    ->label('Draw ID'),
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('s2_winning_number')
                    ->label('S2 (2-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('s3_winning_number')
                    ->label('S3 (3-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('d4_winning_number')
                    ->label('D4 (4-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('coordinator.name')
                    ->searchable()
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
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
