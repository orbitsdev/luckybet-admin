<?php

namespace App\Livewire\Receipt;

use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewReceipt extends Component
{
    
    public Receipt $receipt;
    public array $receiptStats = [];
    
    public function mount(Receipt $receipt): void
    {
        $this->receipt = $receipt;
        
        // Load essential relationships
        $this->receipt->load(['teller', 'location', 'bets.gameType', 'bets.draw']);
        
        // Calculate receipt statistics
        $this->calculateReceiptStats();
    }
    
    public function calculateReceiptStats(): void
    {
        // Get total bets
        $totalBets = $this->receipt->bets()->count();
        
        // Get total amount
        $totalAmount = $this->receipt->total_amount;
        
        // Get bets by game type
        $betsByGameType = [];
        $gameTypeDistribution = [];
        
        foreach ($this->receipt->bets as $bet) {
            $gameTypeCode = $bet->gameType->code;
            $gameTypeName = $bet->gameType->name;
            
            // Handle D4 sub-selection
            $displayGameType = $gameTypeCode;
            if ($gameTypeCode === 'D4' && $bet->d4_sub_selection) {
                $displayGameType = "D4-{$bet->d4_sub_selection}";
            }
            
            if (!isset($betsByGameType[$displayGameType])) {
                $betsByGameType[$displayGameType] = [
                    'name' => $gameTypeName . ($bet->d4_sub_selection ? " ({$bet->d4_sub_selection})" : ""),
                    'count' => 0,
                    'amount' => 0,
                ];
            }
            
            $betsByGameType[$displayGameType]['count']++;
            $betsByGameType[$displayGameType]['amount'] += $bet->amount;
        }
        
        // Get bets by draw time
        $betsByDrawTime = [];
        
        foreach ($this->receipt->bets as $bet) {
            if (!$bet->draw) {
                continue;
            }
            
            $drawTime = $bet->draw->draw_time;
            
            if (!isset($betsByDrawTime[$drawTime])) {
                $betsByDrawTime[$drawTime] = [
                    'count' => 0,
                    'amount' => 0,
                ];
            }
            
            $betsByDrawTime[$drawTime]['count']++;
            $betsByDrawTime[$drawTime]['amount'] += $bet->amount;
        }
        
        // Store the stats
        $this->receiptStats = [
            'total_bets' => $totalBets,
            'total_amount' => $totalAmount,
            'by_game_type' => $betsByGameType,
            'by_draw_time' => $betsByDrawTime,
        ];
    }
    
    public function render(): View
    {
        return view('livewire.receipt.view-receipt');
    }
}
