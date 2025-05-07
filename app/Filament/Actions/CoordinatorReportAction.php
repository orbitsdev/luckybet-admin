<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;

class CoordinatorReportAction extends Action
{
    use HasColor;
    use HasIcon;

    public static function getDefaultName(): ?string
    {
        return 'coordinatorReport';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Coordinator Report');
        $this->color('primary');
        $this->icon('heroicon-o-document-chart-bar');
        $this->button();
        $this->modalWidth('4xl');
        $this->modalHeading('Coordinator Sales Report');
        
        $this->modalContent(function (array $arguments) {
            $coordinatorId = $arguments['coordinator_id'] ?? null;
            $date = $arguments['date'] ?? null;
            
            // Return the view with coordinator data
            return view('filament.actions.coordinator-report-modal', [
                'coordinatorId' => $coordinatorId,
                'date' => $date,
            ]);
        });
    }
}
