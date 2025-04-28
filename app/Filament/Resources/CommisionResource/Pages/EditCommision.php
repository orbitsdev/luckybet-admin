<?php

namespace App\Filament\Resources\CommisionResource\Pages;

use App\Filament\Resources\CommisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommision extends EditRecord
{
    protected static string $resource = CommisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
