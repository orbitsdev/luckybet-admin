<?php

namespace App\Filament\Resources\WinningAmountResource\Pages;

use App\Filament\Resources\WinningAmountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWinningAmount extends EditRecord
{
    protected static string $resource = WinningAmountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
