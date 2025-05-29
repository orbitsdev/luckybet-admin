<?php

namespace App\Livewire\Reports;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class SalesSummary extends Component
{
    public $selectedDate;
    public $coordinators = [];
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $reportData = [];
    public $isGenerating = false;
    public $printableReport = null;
    
    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->coordinators = User::where('role', 'coordinator')->get();
    }
    
    public function generateReport()
    {
        $this->isGenerating = true;
        
        // Get all coordinators with their tellers and bets for the selected date
        $coordinators = User::where('role', 'coordinator')
            ->with(['tellers' => function($query) {
                $query->with(['bets' => function($query) {
                    $query->whereDate('bet_date', $this->selectedDate)
                        ->where('is_rejected', false)
                        ->with(['gameType', 'draw']);
                }]);
            }])
            ->get();
        
        $reportData = [];
        $totalSales = 0;
        $totalHits = 0;
        $totalGross = 0;
        
        foreach ($coordinators as $coordinator) {
            $coordinatorSales = 0;
            $coordinatorHits = 0;
            $coordinatorGross = 0;
            
            foreach ($coordinator->tellers as $teller) {
                $tellerSales = $teller->bets->sum('amount');
                $tellerHits = $this->calculateHits($teller->bets);
                $tellerGross = $tellerSales - $tellerHits;
                
                $coordinatorSales += $tellerSales;
                $coordinatorHits += $tellerHits;
                $coordinatorGross += $tellerGross;
            }
            
            if ($coordinatorSales > 0) {
                $reportData[] = [
                    'coordinator' => $coordinator,
                    'total_sales' => $coordinatorSales,
                    'total_hits' => $coordinatorHits,
                    'total_gross' => $coordinatorGross,
                ];
                
                $totalSales += $coordinatorSales;
                $totalHits += $coordinatorHits;
                $totalGross += $coordinatorGross;
            }
        }
        
        $this->reportData = $reportData;
        $this->totalSales = $totalSales;
        $this->totalHits = $totalHits;
        $this->totalGross = $totalGross;
        $this->isGenerating = false;
    }
    
    private function calculateHits($bets)
    {
        $hits = 0;
        
        foreach ($bets as $bet) {
            $draw = $bet->draw;
            $result = $draw->result;
            
            if (!$result) continue;
            
            $gameTypeCode = $bet->gameType->code ?? '';
            
            if ($gameTypeCode === 'S2' && !empty($result->s2_winning_number)) {
                if ($bet->bet_number === $result->s2_winning_number) {
                    $hits += $bet->winning_amount;
                }
            } elseif ($gameTypeCode === 'S3' && !empty($result->s3_winning_number)) {
                if ($bet->bet_number === $result->s3_winning_number) {
                    $hits += $bet->winning_amount;
                }
            } elseif ($gameTypeCode === 'D4' && !empty($result->d4_winning_number)) {
                if ($bet->d4_sub_selection === 'S2') {
                    if (substr($result->d4_winning_number, -2) === str_pad($bet->bet_number, 2, '0', STR_PAD_LEFT)) {
                        $hits += $bet->winning_amount;
                    }
                } elseif ($bet->d4_sub_selection === 'S3') {
                    if (substr($result->d4_winning_number, -3) === str_pad($bet->bet_number, 3, '0', STR_PAD_LEFT)) {
                        $hits += $bet->winning_amount;
                    }
                } else {
                    if ($bet->bet_number === $result->d4_winning_number) {
                        $hits += $bet->winning_amount;
                    }
                }
            }
        }
        
        return $hits;
    }
    
    public function generatePrintableReport()
    {
        $this->printableReport = [
            'date' => Carbon::parse($this->selectedDate)->format('F d, Y'),
            'coordinators' => $this->reportData,
            'totals' => [
                'sales' => $this->totalSales,
                'hits' => $this->totalHits,
                'gross' => $this->totalGross,
            ]
        ];
    }
    
    public function render()
    {
        return view('livewire.reports.sales-summary');
    }
}
