<?php

namespace App\Filament\Resources\LowWinNumberResource\Pages;

use App\Filament\Resources\LowWinNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLowWinNumber extends EditRecord
{
    protected static string $resource = LowWinNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
