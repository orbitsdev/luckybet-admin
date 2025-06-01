<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\WinningAmount;

class WinningAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all locations to create winning amounts for each location
        $locations = Location::all();
        
        if ($locations->isEmpty()) {
            $locations = collect([
                Location::create([
                    'name' => 'Main Branch',
                    'address' => 'Default Address',
                    'is_active' => true,
                ])
            ]);
        }
        
        // Common bet amounts that customers might use
        $betAmounts = [
            1, 2, 3, 4, 5, 
            10, 15, 20, 25, 30, 40, 50, 
            60, 70, 75, 80, 90, 100, 
            150, 200, 250, 300, 400, 500, 
            600, 700, 800, 900, 1000,
            1500, 2000, 2500, 3000, 5000, 10000
        ];
        
        // Multipliers based on game type
        $multipliers = [
            1 => 70,    // S2 (2D) - 70x
            2 => 450,   // S3 (3D) - 450x
            3 => 4000,  // D4 (4D) - 4000x
        ];
        
        // Create winning amounts for each location, game type, and bet amount
        foreach ($locations as $location) {
            foreach ($multipliers as $gameTypeId => $multiplier) {
                foreach ($betAmounts as $betAmount) {
                    $winningAmount = $betAmount * $multiplier;
                    
                    WinningAmount::updateOrCreate(
                        [
                            'game_type_id' => $gameTypeId,
                            'amount' => $betAmount,
                            'location_id' => $location->id
                        ],
                        ['winning_amount' => $winningAmount]
                    );
                }
            }
        }
    }
}
