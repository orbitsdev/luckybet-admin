<?php

namespace App\Filament\Resources\BetRatioResource\Pages;

use App\Filament\Resources\BetRatioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBetRatios extends ListRecords
{
    protected static string $resource = BetRatioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
