<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\Claim;
use App\Models\Result;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClaimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some winning bets to create claims for
        $result = Result::first();
        
        if (!$result) {
            return; // No results to create claims for
        }
        
        // Get bets that match the winning number based on game type
        $bets = Bet::where('status', 'won')
            ->take(3)
            ->get();
            
        // If no won bets, use active bets
        if ($bets->isEmpty()) {
            $bets = Bet::where('status', 'active')
                ->take(3)
                ->get();
        }
            
        // If no matching bets, create some dummy winning bets
        if ($bets->isEmpty()) {
            // Create a few winning bets
            for ($i = 0; $i < 3; $i++) {
                // Generate a random bet number
                $betNumber = rand(100, 999);
                
                // Get the game type from the draw
                $draw = \App\Models\Draw::find($result->draw_id);
                $gameTypeId = $draw ? $draw->game_type_id : 1; // Default to first game type if draw not found
                
                $bet = Bet::create([
                    'bet_number' => $betNumber,
                    'amount' => rand(20, 100),
                    'draw_id' => $result->draw_id,
                    'game_type_id' => $gameTypeId,
                    'teller_id' => 3, // Teller Jane
                    'location_id' => 1, // Main Branch
                    'bet_date' => now()->format('Y-m-d'),
                    'ticket_id' => 'WIN' . uniqid(),
                    'status' => 'won',
                    'is_combination' => false,
                ]);
                
                $bets->push($bet);
            }
        }
        
        // Create claims for these bets
        foreach ($bets as $bet) {
            // Calculate payout based on game type
            $payout = $bet->amount;
            
            // Get the game type code
            $gameType = $bet->gameType;
            $gameTypeCode = $gameType ? $gameType->code : 'S3'; // Default to S3 if no game type
            
            if ($gameTypeCode == 'S2') {
                $payout *= $bet->is_combination ? 1.5 : 2;
            } elseif ($gameTypeCode == 'S3') {
                $payout *= $bet->is_combination ? 2 : 3;
            } elseif ($gameTypeCode == 'D4') {
                $payout *= $bet->is_combination ? 2.5 : 4;
            }
            
            // Calculate commission (5% of payout)
            $commission = $payout * 0.05;
            
            Claim::create([
                'bet_id' => $bet->id,
                'result_id' => $result->id,
                'teller_id' => $bet->teller_id,
                'amount' => $payout,
                'commission_amount' => $commission,
                'status' => 'processed',
                'claim_at' => now(),
            ]);
            
            // Update bet status to claimed
            $bet->update(['status' => 'claimed']);
        }
    }
}
