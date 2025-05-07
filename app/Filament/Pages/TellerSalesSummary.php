<?php
namespace App\Filament\Pages;

use App\Models\Draw;
use App\Models\Result;
use Filament\Pages\Page;
use App\Services\DrawReportService;

class TellerSalesSummary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.teller-sales-summary';

    public  $summary;
    public string $selectedDate;

    public function mount(): void
    {
        $this->selectedDate = today()->toDateString();
        $this->loadSummary();
    }

    public function updatedSelectedDate(): void
    {
        $this->loadSummary();
    }

    protected function loadSummary(): void
    {
        $this->summary = app(DrawReportService::class)
            ->getSummaryForAll()
            ->toArray();
    }
}
