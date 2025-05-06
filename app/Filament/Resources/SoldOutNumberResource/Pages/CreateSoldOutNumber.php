<?php

namespace App\Filament\Resources\SoldOutNumberResource\Pages;

use App\Filament\Resources\SoldOutNumberResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSoldOutNumber extends CreateRecord
{
    protected static string $resource = SoldOutNumberResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
