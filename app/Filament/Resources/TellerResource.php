<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TellerResource\Pages;
use App\Filament\Resources\TellerResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class TellerResource extends Resource
{
    protected static ?string $model = User::class;
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'teller');
    }

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Tellers';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('commission.rate')
                        ->label('Commission Rate')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1)
                        ->step(0.01)
                        ->default(0.15)
                        ->helperText('Enter as decimal (e.g. 0.15 for 15%)'),
                    Forms\Components\TextInput::make('commission.amount')
                        ->label('Commission Amount')
                        ->numeric()
                        ->nullable()
                        ->helperText('Optional: set a fixed commission amount'),
                ])->relationship('commission'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->mask('99999999999')
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\Select::make('coordinator_id')
                    ->relationship(
                        name: 'coordinator',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('role', 'coordinator')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('location_id')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\Hidden::make('role')
                    ->default('teller'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('commission.rate')
                    ->label('Commission Rate')
                    ->formatStateUsing(fn($state) => $state !== null ? ($state * 100) . '%' : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission.amount')
                    ->label('Commission Amount')
                    ->money('PHP')
                    ->sortable()
                    ->searchable()
                   ,
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coordinator.name')
                    ->label('Coordinator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
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
            'index' => Pages\ListTellers::route('/'),
            'create' => Pages\CreateTeller::route('/create'),
            'edit' => Pages\EditTeller::route('/{record}/edit'),
        ];
    }
}
