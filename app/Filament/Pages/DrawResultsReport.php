<?php
namespace App\Filament\Pages;

use App\Models\Result;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;

class DrawResultsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $navigationLabel = 'Draw Results';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.draw-results-report';

    public $selectedDate;
    public $dateOptions = [];
    
    #[Url]
    public $search = '';
    
    public $perPage = 10;
    public $page = 1;

    public function mount(): void
    {
        // Get all unique draw dates from results
        $this->dateOptions = Result::select('draw_date')
            ->distinct()
            ->orderByDesc('draw_date')
            ->get()
            ->map(function($result) {
                $date = $result->draw_date ? $result->draw_date->format('Y-m-d') : '';
                return [
                    'date' => $date,
                    'label' => $date ? Carbon::parse($date)->format('F j, Y') : 'Unknown Date',
                ];
            })->values()->toArray();

        // Set default to the most recent date
        $this->selectedDate = $this->dateOptions[0]['date'] ?? Carbon::today()->format('Y-m-d');
    }

    public function updatedSelectedDate(): void
    {
        // Reset pagination when date changes
        $this->page = 1;
    }
    
    public function updatedSearch(): void
    {
        // Reset pagination when search changes
        $this->page = 1;
    }

    #[Computed]
    public function results()
    {
        $query = Result::with(['draw'])
            ->where('draw_date', $this->selectedDate);
            
        // Apply search if provided
        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('s2_winning_number', 'like', "%{$search}%")
                  ->orWhere('s3_winning_number', 'like', "%{$search}%")
                  ->orWhere('d4_winning_number', 'like', "%{$search}%");
            });
        }
        
        // Get all results
        $allResults = $query->orderBy('draw_time')->get();
        
        // Map the results
        $mappedResults = $allResults->map(function($result) {
            return [
                'id' => $result->id,
                'draw_date' => $result->draw_date ? $result->draw_date->format('Y-m-d') : '',
                'draw_time' => $result->draw_time ? Carbon::createFromFormat('H:i:s', $result->draw_time)->format('g:i A') : '',
                // Game type is determined by which winning number is present
                'game_type' => $this->determineGameType($result),
                's2_winning_number' => $result->s2_winning_number ?? '-',
                's3_winning_number' => $result->s3_winning_number ?? '-',
                'd4_winning_number' => $result->d4_winning_number ?? '-',
            ];
            })->toArray();
            
        // Apply pagination
        $page = $this->page;
        $perPage = $this->perPage;
        $total = count($mappedResults);
        
        // Get items for current page
        $items = array_slice($mappedResults, ($page - 1) * $perPage, $perPage);
        
        // Create a paginator instance
        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
    
    /**
     * Determine the game type based on which winning number is present
     */
    private function determineGameType($result)
    {
        if (!empty($result->s2_winning_number)) {
            return 'S2';
        } elseif (!empty($result->s3_winning_number)) {
            return 'S3';
        } elseif (!empty($result->d4_winning_number)) {
            return 'D4';
        }
        
        return 'Unknown';
    }
}
