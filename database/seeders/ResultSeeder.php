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
            // Skip if the draw doesn't have a game type
            if (!$draw->gameType) {
                continue;
            }
            
            // Generate winning numbers for each game type
            $s2WinningNumber = sprintf('%02d', rand(0, 99));
            $s3WinningNumber = sprintf('%03d', rand(0, 999));
            $d4WinningNumber = sprintf('%04d', rand(0, 9999));
            
            // Randomly select a coordinator
            $coordinator = $coordinators[array_rand($coordinators->toArray())];
            
            // Create the result with all game types
            Result::create([
                's2_winning_number' => $s2WinningNumber,
                's3_winning_number' => $s3WinningNumber,
                'd4_winning_number' => $d4WinningNumber,
                'draw_id' => $draw->id,
                'game_type_id' => $draw->game_type_id, // Add the game type ID
                'draw_date' => $draw->draw_date,
                'draw_time' => $draw->draw_time,
                'coordinator_id' => $coordinator->id,
            ]);
            
            // Update bets for this draw to won/lost based on winning numbers
            $this->updateBetStatuses($draw->id, $s2WinningNumber, $s3WinningNumber, $d4WinningNumber);
        }
    }
    
    /**
     * Update bet statuses based on winning numbers for all game types
     *
     * @param int $drawId
     * @param string $s2WinningNumber
     * @param string $s3WinningNumber
     * @param string $d4WinningNumber
     * @return void
     */
    private function updateBetStatuses($drawId, $s2WinningNumber, $s3WinningNumber, $d4WinningNumber)
    {
        $bets = \App\Models\Bet::where('draw_id', $drawId)
            ->where('status', 'active')
            ->get();
            
        foreach ($bets as $bet) {
            $isWinner = false;
            
            // Get the appropriate winning number based on the bet's game type
            $winningNumber = '';
            switch ($bet->game_type) {
                case 'S2':
                    $winningNumber = $s2WinningNumber;
                    break;
                case 'S3':
                    $winningNumber = $s3WinningNumber;
                    break;
                case 'D4':
                    $winningNumber = $d4WinningNumber;
                    break;
                default:
                    // If no game type specified, default to S3 (for backward compatibility)
                    $winningNumber = $s3WinningNumber;
            }
            
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
