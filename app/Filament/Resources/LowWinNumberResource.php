<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LowWinNumberResource\Pages;
use App\Models\LowWinNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LowWinNumberResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = LowWinNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationGroup = 'Risk Management';
    protected static ?int $navigationSort = 21;
    protected static ?string $navigationLabel = 'Low Win Numbers';

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
                    ->searchable(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->step(0.01)
                    ->label('Bet Amount'),
                Forms\Components\TextInput::make('winning_amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->step(0.01)
                    ->label('Low Win Amount'),
                Forms\Components\TextInput::make('bet_number')
                    ->maxLength(5)
                    ->label('Bet Number')
                    ->helperText('Leave blank or null for all numbers'),
                Forms\Components\Textarea::make('reason')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpanFull(),
            ]);
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
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable()
                    ->label('Bet Amount'),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->money('PHP')
                    ->sortable()
                    ->label('Low Win Amount'),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('draw_date')
                            ->label('Draw Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['draw_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('draw_date', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Game Type'),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLowWinNumbers::route('/'),
            'create' => Pages\CreateLowWinNumber::route('/create'),
            'edit' => Pages\EditLowWinNumber::route('/{record}/edit'),
        ];
    }    
}
