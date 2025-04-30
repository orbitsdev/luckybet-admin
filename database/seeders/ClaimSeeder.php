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
        
        // Get bets that match the winning number
        $winningNumber = $result->winning_number;
        $bets = Bet::where('bet_number', $winningNumber)
            ->orWhere('bet_number', 'like', "%{$winningNumber}%") // For combination bets
            ->where('status', 'won')
            ->take(3)
            ->get();
            
        // If no matching bets, create some dummy winning bets
        if ($bets->isEmpty()) {
            // Create a few winning bets
            for ($i = 0; $i < 3; $i++) {
                $bet = Bet::create([
                    'bet_number' => $winningNumber,
                    'amount' => rand(20, 100),
                    'draw_id' => $result->draw_id,
                    'teller_id' => 3, // Teller Jane
                    'location_id' => 1, // Main Branch
                    'bet_date' => $result->draw_date,
                    'ticket_id' => 'WIN' . uniqid(),
                    'status' => 'won',
                    'is_combination' => false,
                ]);
                
                $bets->push($bet);
            }
        }
        
        // Create claims for these bets
        foreach ($bets as $bet) {
            // Calculate payout based on bet type
            $payout = $bet->amount;
            if ($bet->draw && $bet->draw->type == 'S2') {
                $payout *= $bet->is_combination ? 1.5 : 2;
            } elseif ($bet->draw && $bet->draw->type == 'S3') {
                $payout *= $bet->is_combination ? 2 : 3;
            } elseif ($bet->draw && $bet->draw->type == 'D4') {
                $payout *= $bet->is_combination ? 2.5 : 4;
            }
            
            Claim::create([
                'bet_id' => $bet->id,
                'result_id' => $result->id,
                'teller_id' => $bet->teller_id,
                'amount' => $payout,
                'status' => 'processed',
                'claimed_at' => now(),
            ]);
            
            // Update bet status to claimed
            $bet->update(['status' => 'claimed']);
        }
    }
}
