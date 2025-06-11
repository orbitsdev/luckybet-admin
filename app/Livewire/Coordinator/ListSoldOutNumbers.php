<?php

namespace App\Livewire\Coordinator;

use App\Models\BetRatio;
use App\Models\GameType;
use App\Models\Location;
use App\Models\Draw;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class ListSoldOutNumbers extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * Register Livewire event listeners using Livewire 3 syntax
     */
    public function __construct()
    {
        // Register event listeners
        $this->listeners = [
            'filament.table.filter' => 'handleFilterChange',
            'filament.table.filters.reset' => 'handleFilterReset',
            'compute-stats' => 'computeStatsListener',
        ];
    }

    /**
     * The currently selected filter date
     *
     * @var string|null
     */
    public $filterDate;
    
    /**
     * Statistics for sold out numbers
     *
     * @var array
     */
    public array $soldOutStats = [];
    
    /**
     * Flag to track if stats computation is in progress
     *
     * @var bool
     */
    protected $computingStats = false;
    
    /**
     * Timestamp of the last stats computation
     *
     * @var int
     */
    protected $lastStatsComputation = 0;
    
    /**
     * Initialize component state
     *
     * @return void
     */
    public function mount()
    {
        // Set default filter date to today
        if (!$this->filterDate) {
            $this->filterDate = now()->toDateString();
        }
        
        $this->computeSoldOutStats();
    }
    
    /**
     * Handle Filament table filter changes
     *
     * @return void
     */
    public function handleFilterChange(): void
    {
        // Get the current filter date or default to today
        $drawDate = $this->tableFilters['draw_date']['value'] ?? now()->toDateString();

        // If the filter was cleared or reset, explicitly set to today
        if (empty($drawDate) || $drawDate === null) {
            $drawDate = now()->toDateString();
            // Update the table filter value to today as well
            $this->tableFilters['draw_date']['value'] = $drawDate;
        }

        // Update filter date
        $this->filterDate = $drawDate;
        
        // Compute stats for the new date
        $this->computeSoldOutStats();

        // Force a refresh to ensure UI is updated
        $this->dispatch('refresh');
    }

    /**
     * Handle explicit filter reset events
     * This is triggered when the user clicks the reset button
     *
     * @return void
     */
    public function handleFilterReset(): void
    {
        // Set filter date to today
        $today = now()->toDateString();
        $this->filterDate = $today;

        // Update the table filter value to today
        if (isset($this->tableFilters['draw_date'])) {
            $this->tableFilters['draw_date']['value'] = $today;
        }
        
        // Compute stats for today
        $this->computeSoldOutStats();

        // Force a refresh of the component
        $this->dispatch('refresh');
    }
    
    /**
     * Livewire listener for the compute-stats event
     *
     * @return void
     */
    public function computeStatsListener(): void
    {
        $this->computeSoldOutStats();
    }
    
    /**
     * Compute sold out numbers statistics
     */
    public function computeSoldOutStats()
    {
        // Use the current filter date or default to today
        $date = $this->filterDate ?: now()->format('Y-m-d');
        $this->filterDate = $date; // Ensure the property is set
        
        // Query to get sold out numbers statistics (BetRatio with max_amount = 0)
        $query = BetRatio::query()
            ->where('max_amount', 0)
            ->whereHas('draw', function ($query) use ($date) {
                $query->whereDate('draw_date', $date);
            })
            ->where('location_id', Auth::user()->location_id)
            ->with(['gameType', 'location']);
        
        // Get total count
        $totalSoldOut = $query->count();
        
        // Store the stats
        $this->soldOutStats = [
            'total_sold_out' => $totalSoldOut
        ];
    }

    public function render(): View
    {
        return view('livewire.coordinator.list-sold-out-numbers');
    }

   
    

}
                        
                       