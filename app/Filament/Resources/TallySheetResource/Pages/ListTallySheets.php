<?php

namespace App\Filament\Resources\TallySheetResource\Pages;

use App\Filament\Resources\TallySheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTallySheets extends ListRecords
{
    protected static string $resource = TallySheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
