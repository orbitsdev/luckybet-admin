<?php

namespace App\Livewire\Reports\Coordinator;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Carbon\Carbon;
use Livewire\WithPagination;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Services\AdminStatisticsService;

class CoordinatorTellerSalesSummary extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use WithPagination;
    use InteractsWithActions;

    public $coordinatorId;
    public $date;
    public $coordinatorData = null;
    public $salesData = [];
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $hasPendingDraws = false;
    public $missingResults = [];

    public function mount($coordinator_id = null)
    {
        $this->coordinatorId = $coordinator_id;
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadCoordinatorData();
        $this->loadSalesData();
    }

    public function loadCoordinatorData()
    {
        $user = Auth::user();
        $coordinatorId = null;

        // Determine which coordinator's tellers to show
        if ($user->role === 'admin' && $this->coordinatorId) {
            // Admin viewing a specific coordinator's tellers
            $coordinatorId = $this->coordinatorId;

            // Get the coordinator data for display
            $coordinator = User::where('id', $coordinatorId)
                ->where('role', 'coordinator')
                ->first();

            if (!$coordinator) {
                // Invalid coordinator ID
                return;
            }

            // Store coordinator data for display
            $this->coordinatorData = [
                'name' => $coordinator->name,
                'id' => $coordinator->id
            ];
        } else if ($user->role === 'coordinator') {
            // Coordinator viewing their own tellers
            $coordinatorId = $user->id;

            $this->coordinatorData = [
                'name' => $user->name,
                'id' => $user->id
            ];
        } else {
            // Fallback to first coordinator if needed
            $coordinator = User::where('role', 'coordinator')->first();
            if ($coordinator) {
                $coordinatorId = $coordinator->id;
                $this->coordinatorData = [
                    'name' => $coordinator->name,
                    'id' => $coordinator->id
                ];
            }
        }

        // Store the coordinator ID for use in other methods
        $this->coordinatorId = $coordinatorId;
    }
public function loadSalesData()
{
    // Reset stats in case of early return
    $this->resetStats();

    if (!$this->coordinatorId || !$this->coordinatorData) {
        return;
    }

    // Get all draws for the date and filter incomplete ones
    $draws = Draw::with('result')
        ->whereDate('draw_date', $this->date)
        ->get();

    // Check completeness and build valid draw IDs list
    $validDraws = [];
    $this->missingResults = [];
    
    foreach ($draws as $draw) {
        $missing = [];

        if (!$draw->result) {
            $missing = ['S2', 'S3', 'D4'];
        } else {
            if (!$draw->result->s2_winning_number) $missing[] = 'S2';
            if (!$draw->result->s3_winning_number) $missing[] = 'S3';
            if (!$draw->result->d4_winning_number) $missing[] = 'D4';
        }

        if (!empty($missing)) {
            $this->missingResults[] = [
                'time' => Carbon::parse($draw->draw_time)->format('g:i A'),
                'missing' => $missing,
            ];
        } else {
            $validDraws[] = $draw;
        }
    }

    $drawIds = collect($validDraws)->pluck('id')->toArray();
    if (empty($drawIds)) {
        return;
    }

    $tellerIds = User::where('coordinator_id', $this->coordinatorId)
        ->where('role', 'teller')
        ->pluck('id')
        ->toArray();
    if (empty($tellerIds)) {
        return;
    }

    // ✅ Check if any draw is missing full result
    $this->missingResults = [];

$draws = Draw::with('result')
    ->whereIn('id', $drawIds)
    ->get();

foreach ($draws as $draw) {
    $missing = [];

    if (!$draw->result) {
        $missing = ['S2', 'S3', 'D4'];
    } else {
        if (!$draw->result->s2_winning_number) $missing[] = 'S2';
        if (!$draw->result->s3_winning_number) $missing[] = 'S3';
        if (!$draw->result->d4_winning_number) $missing[] = 'D4';
    }

    if (!empty($missing)) {
        $this->missingResults[] = [
            'time' => Carbon::parse($draw->draw_time)->format('g:i A'),
            'missing' => $missing,
        ];
    }
}

$this->hasPendingDraws = count($this->missingResults) > 0;
    $reportService = new AdminStatisticsService();
    $summary = $reportService->summarizeByTellers($tellerIds, $drawIds);

    $this->salesData = $summary;
    $this->totalSales = array_sum(array_column($summary, 'total_sales'));
    $this->totalHits = array_sum(array_column($summary, 'total_hits'));
    $this->totalGross = array_sum(array_column($summary, 'total_gross'));
}
private function resetStats()
{
    $this->salesData = [];
    $this->totalSales = 0;
    $this->totalHits = 0;
    $this->totalGross = 0;
    $this->hasPendingDraws = false;
}


    public function updatedDate()
    {
         $this->date = Carbon::parse($this->date)->format('Y-m-d');
        $this->loadSalesData();
    }

    public function viewTellerDetailsAction(): Action
    {
        return Action::make('viewTellerDetails')
            ->label('VIEW DETAILS')
            ->icon('heroicon-o-eye')
            ->color('indigo')
            ->size('xs')
            ->modalHeading(fn (array $arguments) => 'Sales Details for ' . $this->getTellerName($arguments['teller_id']))
            ->modalWidth('7xl')
            ->modalContent(function (array $arguments) {
                $tellerId = $arguments['teller_id'];
                $tellerData = null;

                // Find the teller data in the salesData array
                foreach ($this->salesData as $teller) {
                    if ($teller['id'] == $tellerId) {
                        $tellerData = $teller;
                        break;
                    }
                }

                return view('livewire.reports.coordinator.teller-detailed-sales', [
                    'teller' => $tellerData,
                    'date' => $this->date
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'))
            ->closeModalByClickingAway(true);
    }



    public function viewTellerBetsAction(): Action
    {
        return Action::make('viewTellerBets')
            ->label('VIEW BETS')
            ->icon('heroicon-o-currency-dollar')
            ->color('gray')
            ->size('xs')
            ->url(fn (array $arguments) => route('reports.teller-bets-report', ['teller_id' => $arguments['teller_id'], 'date' => $this->date]))
            ->openUrlInNewTab();
    }

    private function getTellerName($tellerId)
    {
        foreach ($this->salesData as $teller) {
            if ($teller['id'] == $tellerId) {
                return $teller['name'];
            }
        }

        return 'Teller';
    }

    public function render()
    {
        return view('livewire.reports.coordinator.coordinator-teller-sales-summary');
    }
}
