<?php

namespace App\Filament\Resources\LowWinNumberResource\Pages;

use App\Filament\Resources\LowWinNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLowWinNumber extends CreateRecord
{
    protected static string $resource = LowWinNumberResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
