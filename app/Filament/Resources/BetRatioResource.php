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
    // protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = BetRatio::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Betting Management';
    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('draw_id')
                    ->relationship('draw', 'id')
                    ->label('Draw')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Bet Type')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('bet_number')
                    ->label('Bet Number')
                    ->required(),
                Forms\Components\TextInput::make('max_amount')
                    ->label('Max Amount')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('₱')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Teller')
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Location')
                    ->searchable()
                    ->nullable(),

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

       ]   );


    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                \Filament\Tables\Grouping\Group::make('location.name')
                    ->label('Location')
                    ->collapsible(),
            ])
            ->defaultGroup('location.name')
            ->columns([
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Bet Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_amount')
                    ->money('PHP')
                    ->label('Max Amount'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teller')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Location')
                    ->indicator('Location')
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Bet Type')
                    ->indicator('Bet Type')
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('draw_id')
                    ->relationship('draw', 'id')
                    ->label('Draw Date & Time')
                    ->indicator('Draw Date & Time')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        if (!$record->draw_date || !$record->draw_time) return 'Unknown';
                        $date = \Illuminate\Support\Carbon::parse($record->draw_date)->format('M d, Y');
                        $time = \Illuminate\Support\Carbon::createFromFormat('H:i:s', $record->draw_time)->format('g:i A');
                        return "$date ($time)";
                    })
                    ->preload()
                    ->searchable(),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContent)
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
