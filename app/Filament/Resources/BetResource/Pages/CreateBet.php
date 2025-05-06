<?php

namespace App\Filament\Resources\BetResource\Pages;

use App\Filament\Resources\BetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBet extends CreateRecord
{
    protected static string $resource = BetResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
