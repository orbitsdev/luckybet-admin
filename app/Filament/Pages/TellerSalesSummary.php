<?php
namespace App\Filament\Pages;

use App\Models\Draw;
use App\Models\Result;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use App\Services\DrawReportService;

class TellerSalesSummary extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Sales Summary';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.teller-sales-summary';

    public $selectedDrawId;
    public $drawOptions = [];

    public function mount(): void
    {
        $this->drawOptions = Draw::with('result')
            ->orderByDesc('draw_date')
            ->orderByDesc('draw_time')
            ->get()
            ->filter(fn($draw) => $draw->result) // Only draws with results
            ->map(function($draw) {
                $date = $draw->draw_date ? $draw->draw_date->format('F j, Y') : '';
                $time = $draw->draw_time ? \Carbon\Carbon::createFromFormat('H:i:s', $draw->draw_time)->format('g:i A') : '';
                $label = trim($date . ' ' . $time);
                return [
                    'id' => $draw->id,
                    'label' => $label !== '' ? $label : 'Draw #' . $draw->id,
                ];
            })->values()->toArray();

        $this->selectedDrawId = $this->drawOptions[0]['id'] ?? null;
    }

    public function updatedSelectedDrawId(): void
    {
        // No need to call loadSummary() as the computed property will handle it
    }

    #[Computed]
    public function summary()
    {
        return app(DrawReportService::class)
            ->getSummaryForAll($this->selectedDrawId)
            ->toArray();
    }
}
