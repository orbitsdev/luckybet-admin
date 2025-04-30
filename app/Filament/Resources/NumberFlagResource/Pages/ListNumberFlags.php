<?php

namespace App\Filament\Resources\NumberFlagResource\Pages;

use App\Filament\Resources\NumberFlagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNumberFlags extends ListRecords
{
    protected static string $resource = NumberFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
