<?php

namespace App\Filament\Resources\BetRatioResource\Pages;

use App\Filament\Resources\BetRatioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBetRatio extends EditRecord
{
    protected static string $resource = BetRatioResource::class;

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
