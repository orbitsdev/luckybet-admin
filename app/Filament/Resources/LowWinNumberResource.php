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
    protected static ?string $model = LowWinNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-down';
    protected static ?string $navigationGroup = 'Risk Management';
    protected static ?int $navigationSort = 21;
    protected static ?string $navigationLabel = 'Low Win Numbers';

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
                Forms\Components\TimePicker::make('draw_time')
                    ->seconds(true)
                    ->required(),
                Forms\Components\Select::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('bet_number')
                    ->required()
                    ->maxLength(4)
                    ->label('Bet Number')
                    ->helperText('The specific number to mark as low win'),
                Forms\Components\Textarea::make('reason')
                    ->maxLength(255)
                    ->nullable()
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->sortable(),
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
