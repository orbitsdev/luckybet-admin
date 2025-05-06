<?php

namespace App\Filament\Resources\LowWinNumberResource\Pages;

use App\Filament\Resources\LowWinNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLowWinNumbers extends ListRecords
{
    protected static string $resource = LowWinNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
