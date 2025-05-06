<?php

namespace App\Filament\Resources\DrawResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResultRelationManager extends RelationManager
{
    protected static string $relationship = 'result';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('draw_id')
                    ->default(fn ($livewire) => $livewire->ownerRecord->id),
                Forms\Components\Hidden::make('draw_date')
                    ->default(fn ($livewire) => $livewire->ownerRecord->draw_date),
                Forms\Components\Hidden::make('draw_time')
                    ->default(fn ($livewire) => $livewire->ownerRecord->draw_time),
                Forms\Components\Section::make('Winning Numbers')
                    ->description('Enter the winning numbers for each game type')
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
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('draw_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('draw_time')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('s2_winning_number')
                    ->label('S2 (2-Digit)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('s3_winning_number')
                    ->label('S3 (3-Digit)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('d4_winning_number')
                    ->label('D4 (4-Digit)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
