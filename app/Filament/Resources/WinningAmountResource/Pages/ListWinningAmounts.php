<?php

namespace App\Filament\Resources\WinningAmountResource\Pages;

use App\Filament\Resources\WinningAmountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWinningAmounts extends ListRecords
{
    protected static string $resource = WinningAmountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
