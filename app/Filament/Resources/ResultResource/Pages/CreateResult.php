<?php

namespace App\Filament\Resources\ResultResource\Pages;

use App\Filament\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateResult extends CreateRecord
{
    protected static string $resource = ResultResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
{
    // Get the selected draw to copy its date and time
    if (isset($data['draw_id'])) {
        $draw = \App\Models\Draw::find($data['draw_id']);

        if ($draw) {
            // Copy draw_date and draw_time from the selected draw
            $data['draw_date'] = $draw->draw_date;
            $data['draw_time'] = $draw->draw_time;
        }
    }


    return $data;
}
}
