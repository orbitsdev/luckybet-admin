<?php

namespace App\Filament\Resources\BetRatioResource\Pages;

use App\Filament\Resources\BetRatioResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBetRatio extends CreateRecord
{
    protected static string $resource = BetRatioResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
