<?php

namespace App\Filament\Resources\NumberFlagResource\Pages;

use App\Filament\Resources\NumberFlagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNumberFlag extends EditRecord
{
    protected static string $resource = NumberFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
