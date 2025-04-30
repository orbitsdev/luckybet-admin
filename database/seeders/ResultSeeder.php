<?php

namespace Database\Seeders;

use App\Models\Result;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all closed draws that should have results
        $closedDraws = \App\Models\Draw::where('is_open', false)->get();
        if ($closedDraws->isEmpty()) {
            return; // No closed draws to create results for
        }
        
        // Get coordinator users
        $coordinators = \App\Models\User::where('role', 'coordinator')->get();
        if ($coordinators->isEmpty()) {
            $coordinators = [\App\Models\User::where('id', 2)->first()]; // Fallback to Coordinator John
        }
        
        // Create results for each closed draw
        foreach ($closedDraws as $draw) {
            // Generate a winning number based on draw type
            $winningNumber = '';
            $digits = 2; // Default for S2
            
            if ($draw->type === 'S3') {
                $digits = 3;
            } elseif ($draw->type === 'D4') {
                $digits = 4;
            }
            
            for ($i = 0; $i < $digits; $i++) {
                $winningNumber .= rand(0, 9);
            }
            
            // Randomly select a coordinator
            $coordinator = $coordinators[array_rand($coordinators->toArray())];
            
            // Create the result
            Result::create([
                'winning_number' => $winningNumber,
                'draw_id' => $draw->id,
                'draw_date' => $draw->draw_date,
                'coordinator_id' => $coordinator->id,
            ]);
            
            // Update bets for this draw to won/lost based on winning number
            $this->updateBetStatuses($draw->id, $winningNumber);
        }
    }
    
    /**
     * Update bet statuses based on winning number
     *
     * @param int $drawId
     * @param string $winningNumber
     * @return void
     */
    private function updateBetStatuses($drawId, $winningNumber)
    {
        $bets = \App\Models\Bet::where('draw_id', $drawId)
            ->where('status', 'active')
            ->get();
            
        foreach ($bets as $bet) {
            $isWinner = false;
            
            if ($bet->is_combination) {
                // For combination bets, check if any permutation matches
                $betNumber = $bet->bet_number;
                $permutations = $this->generatePermutations($betNumber);
                $isWinner = in_array($winningNumber, $permutations);
            } else {
                // For straight bets, exact match required
                $isWinner = $bet->bet_number === $winningNumber;
            }
            
            // Update bet status
            $bet->update([
                'status' => $isWinner ? 'won' : 'lost'
            ]);
        }
    }
    
    /**
     * Generate all permutations of a number
     *
     * @param string $number
     * @return array
     */
    private function generatePermutations($number)
    {
        $digits = str_split($number);
        $permutations = [];
        
        // Simple implementation for demo purposes
        // For a real app, you'd want a more efficient algorithm
        $this->permute($digits, 0, count($digits) - 1, $permutations);
        
        return $permutations;
    }
    
    /**
     * Helper function to generate permutations
     *
     * @param array $digits
     * @param int $start
     * @param int $end
     * @param array &$result
     * @return void
     */
    private function permute($digits, $start, $end, &$result)
    {
        if ($start === $end) {
            $result[] = implode('', $digits);
        } else {
            for ($i = $start; $i <= $end; $i++) {
                // Swap elements
                $temp = $digits[$start];
                $digits[$start] = $digits[$i];
                $digits[$i] = $temp;
                
                // Recurse
                $this->permute($digits, $start + 1, $end, $result);
                
                // Restore
                $temp = $digits[$start];
                $digits[$start] = $digits[$i];
                $digits[$i] = $temp;
            }
        }
    }
}
