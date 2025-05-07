<?php

namespace App\Filament\Actions;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Filament\Support\Concerns\HasColor;
use Filament\Support\Concerns\HasIcon;

class ViewTallySheetAction extends Action
{
    use HasColor;
    use HasIcon;

    public static function getDefaultName(): ?string
    {
        return 'viewTallySheet';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Tally Sheet');
        $this->color('success');
        $this->icon('heroicon-o-table-cells');
        $this->button();
        $this->modalWidth('7xl');
        $this->modalHeading(fn (array $arguments): string => 'Tally Sheet: ' . ($arguments['coordinatorName'] ?? 'Coordinator'));
        
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
            
            // Get all draws for the selected date
            $draws = Draw::whereDate('draw_date', $date)->get();
            $drawData = [];
            
            foreach ($draws as $draw) {
                $drawTime = Carbon::createFromFormat('H:i:s', $draw->draw_time)->format('g:i A');
                $result = $draw->result;
                
                if (!$result) continue;
                
                $s2WinningNumber = $result->s2_winning_number ?? '-';
                $s3WinningNumber = $result->s3_winning_number ?? '-';
                $d4WinningNumber = $result->d4_winning_number ?? '-';
                
                $drawData[] = [
                    'id' => $draw->id,
                    'time' => $drawTime,
                    's2' => $s2WinningNumber,
                    's3' => $s3WinningNumber,
                    'd4' => $d4WinningNumber,
                ];
            }
            
            // Return the view with tally sheet data
            return view('filament.actions.view-tally-sheet', [
                'coordinator' => $coordinator,
                'date' => Carbon::parse($date)->format('F j, Y'),
                'draws' => $drawData,
            ]);
        });
    }
}
