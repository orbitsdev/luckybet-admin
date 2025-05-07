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
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Claim::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bet_id')
                    ->relationship('bet', 'ticket_id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('result_id')
                    ->relationship('result', 'id')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('teller_id')
                    ->relationship('teller', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱'),
                Forms\Components\TextInput::make('commission_amount')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->default(0.00),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processed' => 'Processed',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('claim_at')
                    ->label('Claim Date/Time'),
                Forms\Components\Textarea::make('qr_code_data')
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bet.ticket_id')
                    ->label('Ticket ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bet.bet_number')
                    ->label('Bet Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('teller.name')
                    ->label('Teller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processed',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('claim_at')
                    ->dateTime()
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
            'index' => Pages\ListClaims::route('/'),
            'create' => Pages\CreateClaim::route('/create'),
            'edit' => Pages\EditClaim::route('/{record}/edit'),
        ];
    }
}
