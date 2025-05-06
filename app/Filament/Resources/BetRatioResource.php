<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BetRatioResource\Pages;
use App\Filament\Resources\BetRatioResource\RelationManagers;
use App\Models\BetRatio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BetRatioResource extends Resource
{
    protected static ?string $model = BetRatio::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Betting Management';
    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('coordinator_id')
                    ->relationship('coordinator', 'name')
                    ->label('Coordinator')
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('draw_date')
                    ->required(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('S2 (2-Digit) Settings')
                            ->schema([
                                Forms\Components\TextInput::make('s2_limit')
                                    ->label('Betting Limit')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('s2_win_amount')
                                    ->label('Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('s2_low_win_amount')
                                    ->label('Low Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                            ]),
                        Forms\Components\Section::make('S3 (3-Digit) Settings')
                            ->schema([
                                Forms\Components\TextInput::make('s3_limit')
                                    ->label('Betting Limit')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('s3_win_amount')
                                    ->label('Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('s3_low_win_amount')
                                    ->label('Low Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                            ]),
                        Forms\Components\Section::make('D4 (4-Digit) Settings')
                            ->schema([
                                Forms\Components\TextInput::make('d4_limit')
                                    ->label('Betting Limit')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('d4_win_amount')
                                    ->label('Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                                Forms\Components\TextInput::make('d4_low_win_amount')
                                    ->label('Low Win Amount')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('₱'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('coordinator.name')
                    ->label('Coordinator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('s2_limit')
                    ->money('PHP')
                    ->label('S2 Limit'),
                Tables\Columns\TextColumn::make('s3_limit')
                    ->money('PHP')
                    ->label('S3 Limit'),
                Tables\Columns\TextColumn::make('d4_limit')
                    ->money('PHP')
                    ->label('D4 Limit'),
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
            RelationManagers\RestrictionsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBetRatios::route('/'),
            'create' => Pages\CreateBetRatio::route('/create'),
            'edit' => Pages\EditBetRatio::route('/{record}/edit'),
        ];
    }    
}
