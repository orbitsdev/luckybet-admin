<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Draw;
use Filament\Tables;
use App\Models\Result;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Resources\ResultResource\RelationManagers;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;
    //should register navigation
    protected static ?string $navigationGroup = 'Draw Management';
    // protected static bool $shouldRegisterNavigation = false;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SELECT DRAW (Visible only on create)
                Forms\Components\Select::make('draw_id')
                    ->relationship(
                        name: 'draw',
                        modifyQueryUsing: function (Builder $query) {
                            // Get all draw IDs that already have results
                            $drawIdsWithResults = \App\Models\Result::select('draw_id')->pluck('draw_id')->toArray();

                            // Exclude draws that already have results
                            return $query->select('id', 'draw_date', 'draw_time')
                                ->whereNotIn('id', $drawIdsWithResults)
                                ->orderByDesc('draw_date')
                                ->orderByDesc('draw_time');
                        }
                    )
                    ->columnSpanFull()
                    ->getOptionLabelFromRecordUsing(fn ($record) =>
                        "Draw #{$record->id} - {$record->draw_date->format('M d, Y')} " .
                        date('h:i A', strtotime($record->draw_time)))
                    ->label('Select Draw')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a draw...')
                    ->optionsLimit(15)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $draw = Draw::find($state);
                            if ($draw) {
                                // Format the date properly for the DatePicker component
                                // The draw_date might be a Carbon instance or a string
                                if ($draw->draw_date instanceof \Carbon\Carbon) {
                                    $set('draw_date', $draw->draw_date->format('Y-m-d'));
                                } else {
                                    $set('draw_date', date('Y-m-d', strtotime($draw->draw_date)));
                                }

                                // Set the time field
                                $set('draw_time', $draw->draw_time);

                                // Log to console for debugging
                                info("Draw selected: {$draw->id}, Date: {$draw->draw_date}, Time: {$draw->draw_time}");
                            }
                        } else {
                            // Clear the fields if no draw is selected
                            $set('draw_date', null);
                            $set('draw_time', null);
                        }
                    })
                    ->visible(fn (string $operation): bool => $operation === 'create'),

                // DISPLAY DRAW INFO ON EDIT
                Forms\Components\TextInput::make('draw_id')
                    ->label('Draw')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record && $record->draw) {
                            $draw = $record->draw;
                            return "Draw #{$draw->id} - {$draw->draw_date->format('M d, Y')} " .
                                   date('h:i A', strtotime($draw->draw_time));
                        }
                        return $state;
                    })
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull()
                    ->visible(fn (string $operation): bool => $operation === 'edit'),

                // COPIED DRAW DATE/TIME (not editable)
                Forms\Components\DatePicker::make('draw_date')
                    ->label('Draw Date')
                    ->format('Y-m-d')
                    ->displayFormat('M d, Y')
                    ->required()
                    ->disabled()
                    ->columnSpan(1)
                    ->dehydrateStateUsing(fn ($state) => $state ? date('Y-m-d', strtotime($state)) : null)
                    ->reactive(),
                Forms\Components\TimePicker::make('draw_time')
                    ->label('Draw Time')
                    ->format('H:i:s')
                    ->displayFormat('h:i A')
                    ->seconds(true)
                    ->required()
                    ->disabled()
                    ->columnSpan(1),

                // WINNING NUMBERS INPUT
                Forms\Components\Section::make('Winning Numbers')
                    ->description('Enter the winning numbers for each game type')
                    ->schema([
                        Forms\Components\TextInput::make('s2_winning_number')
                            ->label('2-Digit (S2)')
                            ->placeholder('00-99')
                            ->mask('99')
                            ->regex('/^\d{2}$/')
                            ->maxLength(2)
                            ->nullable()
                            ->suffixIcon('heroicon-o-trophy'),
                        Forms\Components\TextInput::make('s3_winning_number')
                            ->label('3-Digit (S3)')
                            ->placeholder('000-999')
                            ->mask('999')
                            ->regex('/^\d{3}$/')
                            ->maxLength(3)
                            ->nullable()
                            ->suffixIcon('heroicon-o-trophy'),
                        Forms\Components\TextInput::make('d4_winning_number')
                            ->label('4-Digit (D4)')
                            ->placeholder('0000-9999')
                            ->mask('9999')
                            ->regex('/^\d{4}$/')
                            ->maxLength(4)
                            ->nullable()
                            ->suffixIcon('heroicon-o-trophy'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw.id')
                    ->numeric()
                    ->sortable()
                    ->label('Draw ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw_date')
                    ->date('M d, Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time('h:i A')
                    ->sortable(),
                // Winning numbers with improved display
                Tables\Columns\TextColumn::make('s2_winning_number')
                    ->label('S2 (2-digit)')
                    ->searchable()
                    ->copyable()
                    ->tooltip('2-Digit Winning Number')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ?: '-')
                    ->icon('heroicon-o-trophy'),
                Tables\Columns\TextColumn::make('s3_winning_number')
                    ->label('S3 (3-digit)')
                    ->searchable()
                    ->copyable()
                    ->tooltip('3-Digit Winning Number')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state ?: '-')
                    ->icon('heroicon-o-trophy'),
                Tables\Columns\TextColumn::make('d4_winning_number')
                    ->label('D4 (4-digit)')
                    ->searchable()
                    ->copyable()
                    ->tooltip('4-Digit Winning Number')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => $state ?: '-')
                    ->icon('heroicon-o-trophy'),
                // Created/updated timestamps
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
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
