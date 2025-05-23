<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WinningAmountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example seed values for S2, S3, D4
        $winningAmounts = [
            // S2 (2D) - 750x
            ['game_type_id' => 1, 'amount' => 0.5, 'winning_amount' => 375],
            ['game_type_id' => 1, 'amount' => 1, 'winning_amount' => 750],
            ['game_type_id' => 1, 'amount' => 2, 'winning_amount' => 1500],
            ['game_type_id' => 1, 'amount' => 5, 'winning_amount' => 3750],
            ['game_type_id' => 1, 'amount' => 10, 'winning_amount' => 7500],
            ['game_type_id' => 1, 'amount' => 20, 'winning_amount' => 15000],
            ['game_type_id' => 1, 'amount' => 50, 'winning_amount' => 37500],
            ['game_type_id' => 1, 'amount' => 100, 'winning_amount' => 75000],
            ['game_type_id' => 1, 'amount' => 200, 'winning_amount' => 150000],
            ['game_type_id' => 1, 'amount' => 500, 'winning_amount' => 375000],
            ['game_type_id' => 1, 'amount' => 1000, 'winning_amount' => 750000],

            // S3 (3D) - 4500x
            ['game_type_id' => 2, 'amount' => 0.5, 'winning_amount' => 2250],
            ['game_type_id' => 2, 'amount' => 1, 'winning_amount' => 4500],
            ['game_type_id' => 2, 'amount' => 2, 'winning_amount' => 9000],
            ['game_type_id' => 2, 'amount' => 5, 'winning_amount' => 22500],
            ['game_type_id' => 2, 'amount' => 10, 'winning_amount' => 45000],
            ['game_type_id' => 2, 'amount' => 20, 'winning_amount' => 90000],
            ['game_type_id' => 2, 'amount' => 50, 'winning_amount' => 225000],
            ['game_type_id' => 2, 'amount' => 100, 'winning_amount' => 450000],
            ['game_type_id' => 2, 'amount' => 200, 'winning_amount' => 900000],
            ['game_type_id' => 2, 'amount' => 500, 'winning_amount' => 2250000],
            ['game_type_id' => 2, 'amount' => 1000, 'winning_amount' => 4500000],

            // D4 (4D) - 10000x
            ['game_type_id' => 3, 'amount' => 0.5, 'winning_amount' => 5000],
            ['game_type_id' => 3, 'amount' => 1, 'winning_amount' => 10000],
            ['game_type_id' => 3, 'amount' => 2, 'winning_amount' => 20000],
            ['game_type_id' => 3, 'amount' => 5, 'winning_amount' => 50000],
            ['game_type_id' => 3, 'amount' => 10, 'winning_amount' => 100000],
            ['game_type_id' => 3, 'amount' => 20, 'winning_amount' => 200000],
            ['game_type_id' => 3, 'amount' => 50, 'winning_amount' => 500000],
            ['game_type_id' => 3, 'amount' => 100, 'winning_amount' => 1000000],
            ['game_type_id' => 3, 'amount' => 200, 'winning_amount' => 2000000],
            ['game_type_id' => 3, 'amount' => 500, 'winning_amount' => 5000000],
            ['game_type_id' => 3, 'amount' => 1000, 'winning_amount' => 10000000],
        ];
        $location = \App\Models\Location::first();
        if (!$location) {
            $location = \App\Models\Location::create([
                'name' => 'Main Branch',
                'address' => 'Default Address',
                'is_active' => true,
            ]);
        }
        foreach ($winningAmounts as $data) {
            \App\Models\WinningAmount::updateOrCreate(
                [
                    'game_type_id' => $data['game_type_id'],
                    'amount' => $data['amount'],
                    'location_id' => $location->id
                ],
                ['winning_amount' => $data['winning_amount']]
            );
        }
    }
}
