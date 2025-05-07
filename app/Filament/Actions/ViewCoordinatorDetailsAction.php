<?php

namespace App\Filament\Actions;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;

class ViewCoordinatorDetailsAction extends Action
{
    use HasColor;
    use HasIcon;

    public static function getDefaultName(): ?string
    {
        return 'viewCoordinatorDetails';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Coordinator Sheet');
        $this->color('primary');
        $this->icon('heroicon-o-document-chart-bar');
        $this->button();
        $this->modalWidth('7xl');
        $this->modalHeading(fn (array $arguments): string => 'Coordinator Details: ' . ($arguments['coordinatorName'] ?? 'Coordinator'));
        
        $this->modalContent(function (array $arguments) {
            $coordinatorId = $arguments['coordinatorId'] ?? null;
            $date = $arguments['date'] ?? null;
            
            if (!$coordinatorId || !$date) {
                return 'Missing coordinator ID or date.';
            }
            
            $coordinator = User::find($coordinatorId);
            if (!$coordinator) {
                return 'Coordinator not found.';
            }
            
            // Get all tellers for this coordinator
            $tellers = $coordinator->tellers;
            $tellerData = [];
            $totalSales = 0;
            $totalHits = 0;
            $totalGross = 0;
            
            // For each teller, calculate their sales for the selected date
            foreach ($tellers as $teller) {
                // Get draws for the selected date
                $draws = Draw::whereDate('draw_date', $date)->get();
                $tellerSales = 0;
                $tellerHits = 0;
                
                foreach ($draws as $draw) {
                    // Get bets for this teller and draw
                    $tellerBets = Bet::where('teller_id', $teller->id)
                        ->where('draw_id', $draw->id)
                        ->where('is_rejected', false)
                        ->get();
                    
                    // Calculate sales
                    $drawSales = $tellerBets->sum('amount');
                    $tellerSales += $drawSales;
                    
                    // Calculate hits using the same logic as DrawReportService
                    $drawHits = 0;
                    foreach ($tellerBets as $bet) {
                        $result = $bet->draw->result;
                        if (!$result) continue;
                        
                        // Check if bet number matches winning number based on game type
                        if (
                            ($bet->game_type_id == 1 && $bet->bet_number == $result->s2_winning_number) ||
                            ($bet->game_type_id == 2 && $bet->bet_number == $result->s3_winning_number) ||
                            ($bet->game_type_id == 3 && $bet->bet_number == $result->d4_winning_number)
                        ) {
                            $drawHits += $bet->amount;
                        }
                    }
                    
                    $tellerHits += $drawHits;
                }
                
                // Calculate gross (sales minus hits)
                $tellerGross = $tellerSales - $tellerHits;
                
                $tellerData[] = [
                    'id' => $teller->id,
                    'name' => $teller->name,
                    'total_sales' => $tellerSales,
                    'total_hits' => $tellerHits,
                    'total_gross' => $tellerGross,
                ];
                
                $totalSales += $tellerSales;
                $totalHits += $tellerHits;
                $totalGross += $tellerGross;
            }
            
            // Return the view with coordinator data
            return view('filament.actions.view-coordinator-details', [
                'coordinator' => $coordinator,
                'date' => Carbon::parse($date)->format('F j, Y'),
                'tellers' => $tellerData,
                'totalSales' => $totalSales,
                'totalHits' => $totalHits,
                'totalGross' => $totalGross,
            ]);
        });
    }
}
