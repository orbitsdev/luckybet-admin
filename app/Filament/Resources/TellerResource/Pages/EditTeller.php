<?php

namespace App\Filament\Resources\TellerResource\Pages;

use App\Filament\Resources\TellerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeller extends EditRecord
{
    protected static string $resource = TellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
