<?php

namespace App\Filament\Resources\TellerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\HasOneRelationManager;

class CommissionRelationManager extends HasOneRelationManager
{
    protected static string $relationship = 'commission';
    protected static ?string $recordTitleAttribute = 'rate';
    protected static ?string $title = 'Commission';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('rate')
                ->label('Commission Rate')
                ->numeric()
                ->minValue(0)
                ->maxValue(1)
                ->step(0.01)
                ->default(0.15)
                ->helperText('Enter as decimal (e.g. 0.15 for 15%)'),
            Forms\Components\TextInput::make('amount')
                ->label('Commission Amount')
                ->numeric()
                ->nullable()
                ->helperText('Optional: set a fixed commission amount'),
        ]);
    }
}
