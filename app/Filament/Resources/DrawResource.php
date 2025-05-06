<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrawResource\Pages;
use App\Filament\Resources\DrawResource\RelationManagers;
use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrawResource extends Resource
{
    protected static ?string $model = Draw::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Draw Information')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Forms\Components\DatePicker::make('draw_date')
                                ->required(),
                            Forms\Components\Select::make('draw_time')
                                ->label('Draw Time')
                                ->options(function() {
                                    $schedules = \App\Models\Schedule::where('is_active', true)->get();
                                    $options = [];
                                    foreach ($schedules as $schedule) {
                                        $options[$schedule->draw_time] = "{$schedule->name} ({$schedule->draw_time})";
                                    }
                                    return $options;
                                })
                                ->searchable()
                                ->required(),
                            Forms\Components\Toggle::make('is_open')
                                ->required()->default(true),
                        ]),
                    Forms\Components\Wizard\Step::make('Winning Numbers')
                        ->icon('heroicon-o-trophy')
                        ->schema([
                            Forms\Components\Section::make('Add Winning Numbers')
                                ->description('Enter the winning numbers for this draw')
                                ->schema([
                                    Forms\Components\Group::make()
                                        ->relationship('result')
                                        ->schema([
                                            Forms\Components\TextInput::make('s2_winning_number')
                                                ->label('2-Digit (S2)')
                                                ->placeholder('00-99')
                                                ->maxLength(2)
                                                ->regex('/^\d{2}$/')
                                                ->nullable(),
                                            Forms\Components\TextInput::make('s3_winning_number')
                                                ->label('3-Digit (S3)')
                                                ->placeholder('000-999')
                                                ->maxLength(3)
                                                ->regex('/^\d{3}$/')
                                                ->nullable(),
                                            Forms\Components\TextInput::make('d4_winning_number')
                                                ->label('4-Digit (D4)')
                                                ->placeholder('0000-9999')
                                                ->maxLength(4)
                                                ->regex('/^\d{4}$/')
                                                ->nullable(),
                                        ])
                                        ->columns(3)
                                ])
                        ])
                ])
                ->skippable()->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                // Game type column removed as per documentation
                Tables\Columns\IconColumn::make('is_open')
                    ->boolean(),
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
            RelationManagers\ResultRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDraws::route('/'),
            'create' => Pages\CreateDraw::route('/create'),
            'edit' => Pages\EditDraw::route('/{record}/edit'),
        ];
    }
}
