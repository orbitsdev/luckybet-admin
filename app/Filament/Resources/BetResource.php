<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BetResource\Pages;
use App\Models\Bet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BetResource extends Resource
{
    protected static ?string $model = Bet::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Betting Management';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('draw_id')
                    ->relationship('draw', function ($query) {
                        return $query->select('id', 'draw_date', 'draw_time')
                            ->orderBy('draw_date', 'desc')
                            ->orderBy('draw_time', 'desc');
                    })
                    ->getOptionLabelFromRecordUsing(fn ($record) => 
                        "Draw #{$record->id} - {$record->draw_date->format('M d, Y')} {$record->draw_time}")
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('teller_id')
                    ->relationship('teller', 'name', function ($query) {
                        return $query->where('role', 'teller');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name', function ($query) {
                        return $query->where('role', 'customer');
                    })
                    ->nullable()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('ticket_id')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('bet_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                        'won' => 'Won',
                        'lost' => 'Lost',
                        'claimed' => 'Claimed',
                    ])
                    ->required()
                    ->default('active'),
                Forms\Components\Toggle::make('is_combination')
                    ->required()
                    ->default(false),
                Forms\Components\DateTimePicker::make('bet_date')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_id')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('bet_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'active',
                        'danger' => 'cancelled',
                        'success' => 'won',
                        'warning' => 'lost',
                        'info' => 'claimed',
                    ]),
                Tables\Columns\IconColumn::make('is_combination')
                    ->boolean(),
                Tables\Columns\TextColumn::make('bet_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                        'won' => 'Won',
                        'lost' => 'Lost',
                        'claimed' => 'Claimed',
                    ]),
                Tables\Filters\SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name'),
                Tables\Filters\SelectFilter::make('teller_id')
                    ->relationship('teller', 'name'),
                Tables\Filters\Filter::make('bet_date')
                    ->form([
                        Forms\Components\DatePicker::make('bet_date_from'),
                        Forms\Components\DatePicker::make('bet_date_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bet_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('bet_date', '>=', $date),
                            )
                            ->when(
                                $data['bet_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('bet_date', '<=', $date),
                            );
                    }),
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
            'index' => Pages\ListBets::route('/'),
            'create' => Pages\CreateBet::route('/create'),
            'edit' => Pages\EditBet::route('/{record}/edit'),
        ];
    }
}
