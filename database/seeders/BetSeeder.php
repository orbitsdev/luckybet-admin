<?php

namespace Database\Seeders;

use App\Models\Bet;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all draws
        $draws = \App\Models\Draw::all();
        if ($draws->isEmpty()) {
            return; // No draws to create bets for
        }
        
        // Get all tellers
        $tellers = \App\Models\User::where('role', 'teller')->get();
        if ($tellers->isEmpty()) {
            $tellers = [\App\Models\User::where('id', 3)->first()]; // Fallback to Teller Jane
        }
        
        // Get all locations
        $locations = \App\Models\Location::all();
        if ($locations->isEmpty()) {
            $locations = [\App\Models\Location::where('id', 1)->first()]; // Fallback to Main Branch
        }
        
        // Create bets for each draw
        foreach ($draws as $draw) {
            $betCount = rand(5, 15); // Random number of bets per draw
            $drawDate = $draw->draw_date;
            
            // Get the game type for this draw
            $gameType = $draw->gameType;
            if (!$gameType) {
                continue; // Skip if no game type
            }
            
            // Determine max digits based on game type
            $maxDigits = $gameType->digits;
            
            // Create bets for this draw
            for ($i = 1; $i <= $betCount; $i++) {
                // Generate a bet number with the correct number of digits
                $betNumber = '';
                for ($d = 0; $d < $maxDigits; $d++) {
                    $betNumber .= rand(0, 9);
                }
                
                // Randomly select a teller and location
                $teller = $tellers[array_rand($tellers->toArray())];
                $location = $locations[array_rand($locations->toArray())];
                
                // Determine bet status based on draw date and time
                $status = 'active';
                $now = now();
                // Fix the date parsing by ensuring we have the correct format
                $drawDateTime = \Carbon\Carbon::parse($draw->draw_date->format('Y-m-d') . ' ' . $draw->draw_time);
                
                if ($drawDateTime < $now && !$draw->is_open) {
                    // Draw is in the past and closed
                    // 20% chance of winning, 80% chance of losing
                    $status = (rand(1, 10) <= 2) ? 'won' : 'lost';
                }
                
                // Create the bet
                Bet::create([
                    'bet_number' => $betNumber,
                    'amount' => rand(1, 5) * 20, // Random amount in multiples of 20
                    'draw_id' => $draw->id,
                    'game_type_id' => $gameType->id, // Add the game type ID
                    'teller_id' => $teller->id,
                    'location_id' => $location->id,
                    'bet_date' => $drawDate,
                    'ticket_id' => strtoupper(substr(md5(uniqid()), 0, 10)),
                    'status' => $status,
                    'is_combination' => (rand(1, 10) <= 3), // 30% chance of being a combination bet
                ]);
            }
            
            // For past draws, create some cancelled bets
            if ($drawDateTime < $now) {
                $cancelCount = rand(1, 3);
                for ($i = 1; $i <= $cancelCount; $i++) {
                    $betNumber = '';
                    for ($d = 0; $d < $maxDigits; $d++) {
                        $betNumber .= rand(0, 9);
                    }
                    
                    $teller = $tellers[array_rand($tellers->toArray())];
                    $location = $locations[array_rand($locations->toArray())];
                    
                    Bet::create([
                        'bet_number' => $betNumber,
                        'amount' => rand(1, 5) * 20,
                        'draw_id' => $draw->id,
                        'teller_id' => $teller->id,
                        'location_id' => $location->id,
                        'bet_date' => $drawDate,
                        'ticket_id' => strtoupper(substr(md5(uniqid()), 0, 10)),
                        'status' => 'cancelled',
                        'is_combination' => (rand(1, 10) <= 3),
                    ]);
                }
            }
        }
    }
}
