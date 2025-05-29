<?php

namespace App\Livewire\Reports;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportByCoordinator extends Component
{
    public $selectedDate;
    public $selectedCoordinator;
    public $coordinators = [];
    public $drawTimes = [];
    public $selectedDrawTime = null;
    public $reportData = [];
    public $tellerReports = [];
    public $isGenerating = false;
    public $printableReport = null;
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $viewingTellerDetails = false;
    public $selectedTeller = null;
    public $tellerDetailData = [];
    
    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->coordinators = User::where('role', 'coordinator')->get();
        $this->loadDrawTimes();
    }
    
    public function loadDrawTimes()
    {
        $this->drawTimes = Draw::whereDate('draw_date', $this->selectedDate)
            ->orderBy('draw_time')
            ->get()
            ->map(function ($draw) {
                return [
                    'id' => $draw->id,
                    'time' => $draw->draw_time,
                    'formatted_time' => Carbon::parse($draw->draw_time)->format('g:i A'),
                    'label' => Carbon::parse($draw->draw_time)->format('g:i A') . ' / ' . $draw->id
                ];
            })
            ->toArray();
    }
    
    public function updatedSelectedDate()
    {
        $this->loadDrawTimes();
        $this->selectedDrawTime = null;
        $this->reportData = [];
        $this->tellerReports = [];
    }
    
    public function updatedSelectedCoordinator()
    {
        $this->reportData = [];
        $this->tellerReports = [];
    }
    
    public function generateReport()
    {
        if (!$this->selectedCoordinator) {
            return;
        }
        
        $this->isGenerating = true;
        $this->viewingTellerDetails = false;
        $this->selectedTeller = null;
        
        $coordinator = User::where('id', $this->selectedCoordinator)
            ->with(['tellers' => function($query) {
                $query->with(['bets' => function($query) {
                    $query->whereDate('bet_date', $this->selectedDate)
                        ->where('is_rejected', false)
                        ->when($this->selectedDrawTime, function($q) {
                            return $q->whereHas('draw', function($q) {
                                $q->where('id', $this->selectedDrawTime);
                            });
                        })
                        ->with(['gameType', 'draw']);
                }]);
            }])
            ->first();
        
        if (!$coordinator) {
            $this->isGenerating = false;
            return;
        }
        
        $reportData = [];
        $tellerReports = [];
        $totalSales = 0;
        $totalHits = 0;
        $totalGross = 0;
        
        // Group by draw time
        $drawTimeGroups = [];
        
        foreach ($coordinator->tellers as $teller) {
            $tellerSales = 0;
            $tellerHits = 0;
            $tellerGross = 0;
            $tellerDrawData = [];
            
            // Group bets by draw
            $betsByDraw = $teller->bets->groupBy(function($bet) {
                return $bet->draw->id;
            });
            
            foreach ($betsByDraw as $drawId => $drawBets) {
                $draw = $drawBets->first()->draw;
                $drawTime = $draw->draw_time;
                $formattedTime = Carbon::parse($drawTime)->format('g:i A');
                
                $drawSales = $drawBets->sum('amount');
                $drawHits = $this->calculateHits($drawBets);
                $drawGross = $drawSales - $drawHits;
                
                $tellerDrawData[] = [
                    'draw_id' => $drawId,
                    'draw_time' => $drawTime,
                    'formatted_time' => $formattedTime,
                    'sales' => $drawSales,
                    'hits' => $drawHits,
                    'gross' => $drawGross,
                ];
                
                $tellerSales += $drawSales;
                $tellerHits += $drawHits;
                $tellerGross += $drawGross;
                
                // Add to draw time groups for the main report
                if (!isset($drawTimeGroups[$drawTime])) {
                    $drawTimeGroups[$drawTime] = [
                        'time' => $drawTime,
                        'formatted_time' => $formattedTime,
                        'sales' => 0,
                        'hits' => 0,
                        'gross' => 0,
                    ];
                }
                
                $drawTimeGroups[$drawTime]['sales'] += $drawSales;
                $drawTimeGroups[$drawTime]['hits'] += $drawHits;
                $drawTimeGroups[$drawTime]['gross'] += $drawGross;
            }
            
            if ($tellerSales > 0) {
                $tellerReports[] = [
                    'teller' => $teller,
                    'total_sales' => $tellerSales,
                    'total_hits' => $tellerHits,
                    'total_gross' => $tellerGross,
                    'draw_data' => $tellerDrawData,
                ];
                
                $totalSales += $tellerSales;
                $totalHits += $tellerHits;
                $totalGross += $tellerGross;
            }
        }
        
        // Convert draw time groups to array for the report
        foreach ($drawTimeGroups as $drawTime => $data) {
            $reportData[] = $data;
        }
        
        // Sort by draw time
        usort($reportData, function($a, $b) {
            return strcmp($a['time'], $b['time']);
        });
        
        $this->reportData = $reportData;
        $this->tellerReports = $tellerReports;
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
    
    public function viewTellerDetails($tellerId)
    {
        $this->selectedTeller = $this->tellerReports[array_search($tellerId, array_column($this->tellerReports, 'teller.id'))];
        $this->viewingTellerDetails = true;
    }
    
    public function backToCoordinatorView()
    {
        $this->viewingTellerDetails = false;
        $this->selectedTeller = null;
    }
    
    public function generatePrintableReport()
    {
        $coordinator = User::find($this->selectedCoordinator);
        
        $this->printableReport = [
            'date' => Carbon::parse($this->selectedDate)->format('F d, Y'),
            'coordinator' => $coordinator ? $coordinator->name : 'Unknown',
            'draw_time' => $this->selectedDrawTime ? Draw::find($this->selectedDrawTime)->draw_time : null,
            'tellers' => $this->tellerReports,
            'totals' => [
                'sales' => $this->totalSales,
                'hits' => $this->totalHits,
                'gross' => $this->totalGross,
            ]
        ];
    }
    
    public function render()
    {
        return view('livewire.reports.report-by-coordinator');
    }
}
