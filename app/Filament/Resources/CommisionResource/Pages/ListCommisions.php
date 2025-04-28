<?php

namespace App\Filament\Resources\CommisionResource\Pages;

use App\Filament\Resources\CommisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommisions extends ListRecords
{
    protected static string $resource = CommisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
