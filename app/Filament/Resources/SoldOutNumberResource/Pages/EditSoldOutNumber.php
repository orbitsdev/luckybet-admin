<?php

namespace App\Filament\Resources\SoldOutNumberResource\Pages;

use App\Filament\Resources\SoldOutNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoldOutNumber extends EditRecord
{
    protected static string $resource = SoldOutNumberResource::class;

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
