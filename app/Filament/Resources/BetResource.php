<?php

namespace App\Filament\Resources;

use App\Models\Bet;
use Filament\Forms;
use Filament\Tables;
use App\Models\GameType;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
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
                // Main section with draw and game type selection
                Forms\Components\Section::make('Draw Information')
                    ->description('Select the draw and game type for this bet')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('draw_id')
                                    ->label('Draw Date & Time')
                                    ->relationship('draw', function ($query) {
                                        return $query->select('id', 'draw_date', 'draw_time')
                                            ->orderBy('draw_date', 'desc')
                                            ->orderBy('draw_time', 'desc');
                                    })
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        $formattedTime = \Carbon\Carbon::createFromFormat('H:i:s', $record->draw_time)->format('g:i A');
                                        return "{$record->draw_date->format('M d, Y')} ({$formattedTime})";
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('game_type_id')
                                    ->label('Game Type')
                                    ->relationship('gameType', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $state, $context) {
                                        $set('bet_number', '');
                                    })
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->collapsible(),

                // Bet details section
                Forms\Components\Section::make('Bet Details')
                    ->description('Enter the bet number and amount')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
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
                                        $gameType = GameType::find($get('game_type_id'));
                                        if (!$gameType) return null;
                                        
                                        // Create a mask with the correct number of digits (9's)
                                        return str_repeat('9', $gameType->digit_count);
                                    })
                                    ->placeholder(function ($get) {
                                        $gameType = GameType::find($get('game_type_id'));
                                        if (!$gameType) return 'Select game type first';
                                        
                                        return "Enter {$gameType->digit_count} digits";
                                    }),
                                Forms\Components\TextInput::make('amount')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₱'),
                            ]),
                        // Forms\Components\Toggle::make('format_bet_number')
                        //     ->label('Format Number')
                        //     ->helperText('Toggle ON to format the bet number with leading zeros')
                        //     ->default(true)
                        //     ->live()
                        //     ->afterStateUpdated(function ($set, $state, $get) {
                        //         // Re-trigger the bet number formatting
                        //         $set('bet_number', $get('bet_number'));
                        //     }),
                        Forms\Components\Toggle::make('is_combination')
                            ->label('Combination Bet')
                            ->helperText('Toggle ON if this bet is a combination bet (allows winning with different number arrangements)')
                            ->required()
                            ->default(false),
                        Forms\Components\Select::make('d4_sub_selection')
                            ->label('D4 Sub-Selection')
                            ->options([
                                'S2' => 'S2 (First 2 Digits)',
                                'S3' => 'S3 (First 3 Digits)'
                            ])
                            ->helperText('Only applicable for D4 game type with 9 PM draw')
                            ->visible(function ($get) {
                                // Get the game type ID and draw ID
                                $gameTypeId = $get('game_type_id');
                                $drawId = $get('draw_id');
                                
                                // If either is not set, hide the field
                                if (!$gameTypeId || !$drawId) {
                                    return false;
                                }
                                
                                // Check if game type is D4
                                $gameType = GameType::find($gameTypeId);
                                if (!$gameType || $gameType->code !== 'D4') {
                                    return false;
                                }
                                
                                // Check if draw time is 9 PM
                                $draw = \App\Models\Draw::find($drawId);
                                if (!$draw) {
                                    return false;
                                }
                                
                                // Check if the draw time is 9 PM (21:00:00)
                                $drawTime = \Carbon\Carbon::createFromFormat('H:i:s', $draw->draw_time);
                                return $drawTime->format('H:i') === '21:00';
                            }),
                    ]),

                // Customer and location information
                Forms\Components\Section::make('Customer & Location')
                    ->description('Select the teller, customer, and location')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('teller_id')
                                    ->label('Teller')
                                    ->relationship('teller', 'name', function ($query) {
                                        return $query->where('role', 'teller');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name', function ($query) {
                                        return $query->where('role', 'customer');
                                    })
                                    ->nullable()
                                    ->searchable()
                                    ->preload(),
                            ]),
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->relationship('location', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('bet_date')
                            ->label('Bet Date & Time')
                            ->required()
                            ->default(now()),
                    ])
                    ->collapsible(),

                // Bet status section
                Forms\Components\Section::make('Bet Status')
                    ->description('Manage the current status of this bet')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_claimed')
                                    ->label('Claimed')
                                    ->helperText('Toggle ON if this bet has been claimed by the customer')
                                    ->required()
                                    ->default(false),
                                Forms\Components\Toggle::make('is_rejected')
                                    ->label('Rejected/Cancelled')
                                    ->helperText('Toggle ON if this bet has been cancelled or rejected')
                                    ->required()
                                    ->default(false),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Primary information - most important columns first
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
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('gameType.name')
                    ->label('Game Type')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('winning_amount')
                    ->label('Winning Amount')
                    ->money('PHP')
                    ->sortable()
                    ->alignRight(),

                // Status indicators - grouped together
                Tables\Columns\IconColumn::make('is_claimed')
                    ->label('Claimed')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_rejected')
                    ->label('Rejected')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_combination')
                    ->label('Combination')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('d4_sub_selection')
                    ->label('Sub-Type')
                    ->badge()
                    ->color('primary')
                ,

                // Draw information - grouped together
                Tables\Columns\TextColumn::make('draw.draw_date')
                    ->label('Draw Date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw.draw_time')
                    ->label('Draw Time')
                    ->time('h:i A')
                    ->sortable(),

                // Secondary information
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Ticket ID copied to clipboard')
                    ->copyMessageDuration(1500),

                // Timestamps - less important, at the end
                Tables\Columns\TextColumn::make('bet_date')
                    ->label('Bet Date & Time')
                    ->dateTime('M d, Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y - h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Status filters
                Tables\Filters\Filter::make('is_claimed')
                    ->label('Claimed Status')
                    ->indicator('Claimed')
                    ->form([
                        Forms\Components\Select::make('is_claimed')
                            ->label('Claimed Status')
                            ->options([
                                '1' => 'Claimed',
                                '0' => 'Not Claimed',
                            ])
                            ->placeholder('All')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['is_claimed'] !== null,
                            fn (Builder $query): Builder => $query->where('is_claimed', $data['is_claimed']),
                        );
                    }),
                Tables\Filters\Filter::make('is_rejected')
                    ->label('Rejected Status')
                    ->indicator('Rejected')
                    ->form([
                        Forms\Components\Select::make('is_rejected')
                            ->label('Rejected Status')
                            ->options([
                                '1' => 'Rejected',
                                '0' => 'Not Rejected',
                            ])
                            ->placeholder('All')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['is_rejected'] !== null,
                            fn (Builder $query): Builder => $query->where('is_rejected', $data['is_rejected']),
                        );
                    }),

                // Bet information filters
                Tables\Filters\SelectFilter::make('game_type_id')
                    ->relationship('gameType', 'name')
                    ->label('Game Type')
                    ->indicator('Game Type')
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('teller_id')
                    ->relationship('teller', 'name')
                    ->label('Teller')
                    ->indicator('Teller')
                    ->preload()
                    ->searchable(),

                // Date range filter
                Tables\Filters\Filter::make('bet_date')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('bet_date_from')
                                    ->label('From Date')
                                    ->placeholder('Start date')
                                    ->native(false),
                                Forms\Components\DatePicker::make('bet_date_until')
                                    ->label('To Date')
                                    ->placeholder('End date')
                                    ->native(false),
                            ]),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        $indicators = [];

                        if ($data['bet_date_from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['bet_date_from'])->format('M d, Y');
                        }

                        if ($data['bet_date_until'] ?? null) {
                            $indicators[] = 'To ' . \Carbon\Carbon::parse($data['bet_date_until'])->format('M d, Y');
                        }

                        return count($indicators) > 0 ? implode(' - ', $indicators) : null;
                    })
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
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\Action::make('claim')
                        ->label('Mark as Claimed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Bet $record): bool => !$record->is_claimed && !$record->is_rejected)
                        ->action(function (Bet $record): void {
                            $record->is_claimed = true;
                            $record->save();
                            Notification::make()
                                ->title('Bet marked as claimed')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->label('Mark as Rejected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Bet $record): bool => !$record->is_claimed && !$record->is_rejected)
                        ->action(function (Bet $record): void {
                            $record->is_rejected = true;
                            $record->save();
                            Notification::make()
                                ->title('Bet marked as rejected')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->modifyQueryUsing(fn (Builder $query): Builder => $query->latest());
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
