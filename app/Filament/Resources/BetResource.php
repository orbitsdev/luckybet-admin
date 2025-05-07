<?php

namespace App\Filament\Resources;

use App\Models\Bet;
use App\Models\GameType;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BetResource\Pages;

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
                    ->live()
                    ->afterStateUpdated(function ($set, $state, $context) {
                        $set('bet_number', '');
                    })
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
                 
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    Forms\Components\TextInput::make('bet_number')
                    ->label('Bet Number')
                    ->required()
                    ->maxLength(4) // Max is 4, since D4 is highest
                    ->reactive() // ensures mask can react to game_type_id change
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        // Keep only digits
                        $state = preg_replace('/\D/', '', $state);
                        $set('bet_number', $state);
                    })
                    ->mask(function ($get) {
                        $code = optional(GameType::find($get('game_type_id')))->code;
                        return match ($code) {
                            'S2' => '99',
                            'S3' => '999',
                            'D4' => '9999',
                            default => null,
                        };
                    }),
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
                    ->label('Ticket ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Ticket ID copied to clipboard')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bet_number')
                    ->label('Bet Number')
                    ->formatStateUsing(function ($state, $record) {
                        $code = $record->gameType->code ?? null;
                        // Format based on game type
                        return match ($code) {
                            'S2' => str_pad($state, 2, '0', STR_PAD_LEFT),
                            'S3' => str_pad($state, 3, '0', STR_PAD_LEFT),
                            'D4' => str_pad($state, 4, '0', STR_PAD_LEFT),
                            default => $state,
                        };
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->colors([
                        'primary' => 'active',
                        'danger' => 'cancelled',
                        'success' => 'won',
                        'warning' => 'lost',
                        'info' => 'claimed',
                    ]),
                Tables\Columns\IconColumn::make('is_combination')
                    ->label('Combination')
                    ->boolean(),
                Tables\Columns\TextColumn::make('bet_date')
                    ->label('Bet Date & Time')
                    ->dateTime('M d, Y - h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y - h:i A')
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
