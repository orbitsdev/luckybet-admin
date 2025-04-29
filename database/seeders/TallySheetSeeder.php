<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\User;
use App\Models\TallySheet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TallySheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teller users
        $tellers = User::where('role', 'teller')->get();
        
        // Create tally sheets for the last 7 days
        for ($day = 0; $day < 7; $day++) {
            $date = now()->subDays($day)->format('Y-m-d');
            
            foreach ($tellers as $teller) {
                // Get location ID (assuming tellers are assigned to locations)
                $locationId = 1; // Default to first location
                
                // Calculate totals based on bets for this teller on this date
                $totalSales = Bet::where('teller_id', $teller->id)
                    ->whereDate('bet_date', $date)
                    ->sum('amount');
                
                // For demo purposes, calculate some sample values
                $totalClaims = $totalSales * 0.3; // 30% of sales are claimed as winnings
                $totalCommission = $totalSales * 0.1; // 10% commission
                $netAmount = $totalSales - $totalClaims - $totalCommission;
                
                // Create tally sheet
                TallySheet::create([
                    'teller_id' => $teller->id,
                    'location_id' => $locationId,
                    'sheet_date' => $date,
                    'total_sales' => $totalSales > 0 ? $totalSales : rand(500, 2000), // Random amount if no bets
                    'total_claims' => $totalClaims > 0 ? $totalClaims : rand(100, 500),
                    'total_commission' => $totalCommission > 0 ? $totalCommission : rand(50, 200),
                    'net_amount' => $netAmount > 0 ? $netAmount : rand(300, 1500),
                ]);
            }
        }
    }
}
