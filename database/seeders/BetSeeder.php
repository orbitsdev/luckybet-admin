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
        $draws = \App\Models\Draw::with('gameType')->get();
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
            
            // For now, assign a default game_type_id for each bet (e.g., S3)
            $defaultGameTypeId = 1; // You may want to randomize or assign properly
            $maxDigits = 3; // Default to 3 digits (S3)
            // If you want to randomize, you can fetch game types and select one here
            
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
                $now = now();
                $drawDateTime = \Carbon\Carbon::parse($draw->draw_date->format('Y-m-d') . ' ' . $draw->draw_time);
                $is_claimed = false;
                $is_rejected = false;
                // For past draws, you may want to simulate some claimed bets, but default to false
                
                // Create the bet
                Bet::create([
                    'bet_number' => $betNumber,
                    'amount' => rand(1, 5) * 20, // Random amount in multiples of 20
                    'draw_id' => $draw->id,
                    'game_type_id' => $gameType->id, // Add the game type ID
                    'teller_id' => $teller->id,
                    'location_id' => $location->id,
                    'bet_date' => $drawDate,
                    'is_claimed' => $is_claimed,
                    'is_rejected' => $is_rejected,
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
                        'game_type_id' => $gameType->id, // Add the game type ID
                        'teller_id' => $teller->id,
                        'location_id' => $location->id,
                        'bet_date' => $drawDate,
                        'is_claimed' => false,
                        'is_rejected' => true,
                    ]);
                }
            }
        }
    }
}
