<?php

namespace App\Filament\Resources\SoldOutNumberResource\Pages;

use App\Filament\Resources\SoldOutNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoldOutNumbers extends ListRecords
{
    protected static string $resource = SoldOutNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
