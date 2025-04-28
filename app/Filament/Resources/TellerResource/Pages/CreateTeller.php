<?php

namespace App\Filament\Resources\TellerResource\Pages;

use App\Filament\Resources\TellerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTeller extends CreateRecord
{
    protected static string $resource = TellerResource::class;
}
