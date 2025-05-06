<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResultResource\Pages;
use App\Filament\Resources\ResultResource\RelationManagers;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

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
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $draw = \App\Models\Draw::find($state);
                            if ($draw) {
                                $set('draw_date', $draw->draw_date);
                                $set('draw_time', $draw->draw_time);
                            }
                        }
                    }),
                Forms\Components\DatePicker::make('draw_date')
                    ->required()
                    ->disabled(),
                Forms\Components\TimePicker::make('draw_time')
                    ->seconds(true)
                    ->required()
                    ->disabled(),
                Forms\Components\Section::make('Winning Numbers')
                    ->description('Enter the winning numbers for each game type')
                    ->schema([
                        Forms\Components\TextInput::make('s2_winning_number')
                            ->label('2-Digit (S2)')
                            ->placeholder('00-99')
                            ->helperText('Enter exactly 2 digits')
                            ->maxLength(2)
                            ->regex('/^\d{2}$/')
                            ->nullable(),
                        Forms\Components\TextInput::make('s3_winning_number')
                            ->label('3-Digit (S3)')
                            ->placeholder('000-999')
                            ->helperText('Enter exactly 3 digits')
                            ->maxLength(3)
                            ->regex('/^\d{3}$/')
                            ->nullable(),
                        Forms\Components\TextInput::make('d4_winning_number')
                            ->label('4-Digit (D4)')
                            ->placeholder('0000-9999')
                            ->helperText('Enter exactly 4 digits')
                            ->maxLength(4)
                            ->regex('/^\d{4}$/')
                            ->nullable(),
                    ])
                    ->columns(3),
                // Coordinator field removed as per new structure
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw.id')
                    ->numeric()
                    ->sortable()
                    ->label('Draw ID'),
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('s2_winning_number')
                    ->label('S2 (2-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('s3_winning_number')
                    ->label('S3 (3-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('d4_winning_number')
                    ->label('D4 (4-digit)')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('danger'),
                // Coordinator column removed as per new structure
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
            'index' => Pages\ListResults::route('/'),
            'create' => Pages\CreateResult::route('/create'),
            'edit' => Pages\EditResult::route('/{record}/edit'),
        ];
    }
}
