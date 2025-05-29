<?php

namespace App\Livewire\Reports;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportByTeller extends Component
{
    public $selectedDate;
    public $selectedTeller;
    public $tellers = [];
    public $drawTimes = [];
    public $selectedDrawTime = null;
    public $reportData = [];
    public $isGenerating = false;
    public $printableReport = null;
    public $totalSales = 0;
    public $totalHits = 0;
    public $totalGross = 0;
    public $gameTypeData = [];
    
    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->tellers = User::where('role', 'teller')->get();
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
        $this->gameTypeData = [];
    }
    
    public function updatedSelectedTeller()
    {
        $this->reportData = [];
        $this->gameTypeData = [];
    }
    
    public function generateReport()
    {
        if (!$this->selectedTeller) {
            return;
        }
        
        $this->isGenerating = true;
        
        $teller = User::where('id', $this->selectedTeller)
            ->with(['bets' => function($query) {
                $query->whereDate('bet_date', $this->selectedDate)
                    ->where('is_rejected', false)
                    ->when($this->selectedDrawTime, function($q) {
                        return $q->whereHas('draw', function($q) {
                            $q->where('id', $this->selectedDrawTime);
                        });
                    })
                    ->with(['gameType', 'draw']);
            }])
            ->first();
        
        if (!$teller) {
            $this->isGenerating = false;
            return;
        }
        
        $reportData = [];
        $gameTypeData = [];
        $totalSales = 0;
        $totalHits = 0;
        $totalGross = 0;
        
        // Group by draw time
        $drawTimeGroups = [];
        
        // Group by game type
        $gameTypeGroups = [];
        
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
            
            $reportData[] = [
                'draw_id' => $drawId,
                'draw_time' => $drawTime,
                'formatted_time' => $formattedTime,
                'sales' => $drawSales,
                'hits' => $drawHits,
                'gross' => $drawGross,
            ];
            
            $totalSales += $drawSales;
            $totalHits += $drawHits;
            $totalGross += $drawGross;
            
            // Group by game type
            $betsByGameType = $drawBets->groupBy(function($bet) {
                return $bet->gameType->code;
            });
            
            foreach ($betsByGameType as $gameTypeCode => $gameBets) {
                if (!isset($gameTypeGroups[$gameTypeCode])) {
                    $gameTypeGroups[$gameTypeCode] = [
                        'code' => $gameTypeCode,
                        'name' => $gameBets->first()->gameType->name,
                        'sales' => 0,
                        'hits' => 0,
                        'gross' => 0,
                    ];
                }
                
                $gameTypeSales = $gameBets->sum('amount');
                $gameTypeHits = $this->calculateHits($gameBets);
                $gameTypeGross = $gameTypeSales - $gameTypeHits;
                
                $gameTypeGroups[$gameTypeCode]['sales'] += $gameTypeSales;
                $gameTypeGroups[$gameTypeCode]['hits'] += $gameTypeHits;
                $gameTypeGroups[$gameTypeCode]['gross'] += $gameTypeGross;
            }
        }
        
        // Convert game type groups to array for the report
        foreach ($gameTypeGroups as $gameTypeCode => $data) {
            $gameTypeData[] = $data;
        }
        
        // Sort by draw time
        usort($reportData, function($a, $b) {
            return strcmp($a['draw_time'], $b['draw_time']);
        });
        
        $this->reportData = $reportData;
        $this->gameTypeData = $gameTypeData;
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
        $teller = User::find($this->selectedTeller);
        
        $this->printableReport = [
            'date' => Carbon::parse($this->selectedDate)->format('F d, Y'),
            'teller' => $teller ? $teller->name : 'Unknown',
            'draw_time' => $this->selectedDrawTime ? Draw::find($this->selectedDrawTime)->draw_time : null,
            'draws' => $this->reportData,
            'game_types' => $this->gameTypeData,
            'totals' => [
                'sales' => $this->totalSales,
                'hits' => $this->totalHits,
                'gross' => $this->totalGross,
            ]
        ];
    }
    
    public function render()
    {
        return view('livewire.reports.report-by-teller');
    }
}
