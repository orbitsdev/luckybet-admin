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
            // S2 (multiplier: 750x)
            ['game_type_id' => 1, 'amount' => 0.5, 'winning_amount' => 375],
            ['game_type_id' => 1, 'amount' => 1, 'winning_amount' => 750],
            ['game_type_id' => 1, 'amount' => 2, 'winning_amount' => 1500],
            ['game_type_id' => 1, 'amount' => 5, 'winning_amount' => 3750],
            ['game_type_id' => 1, 'amount' => 10, 'winning_amount' => 7500],
            ['game_type_id' => 1, 'amount' => 20, 'winning_amount' => 15000],
            ['game_type_id' => 1, 'amount' => 50, 'winning_amount' => 37500],
            ['game_type_id' => 1, 'amount' => 100, 'winning_amount' => 75000],
            ['game_type_id' => 1, 'amount' => 200, 'winning_amount' => 150000],
            ['game_type_id' => 1, 'amount' => 330, 'winning_amount' => 247500],
            ['game_type_id' => 1, 'amount' => 500, 'winning_amount' => 375000],
            ['game_type_id' => 1, 'amount' => 1000, 'winning_amount' => 750000],
            ['game_type_id' => 1, 'amount' => 2000, 'winning_amount' => 1500000],
            ['game_type_id' => 1, 'amount' => 5000, 'winning_amount' => 3750000],
            ['game_type_id' => 1, 'amount' => 10000, 'winning_amount' => 7500000],
            ['game_type_id' => 1, 'amount' => 20000, 'winning_amount' => 15000000],

            // S3 (multiplier: 4500x)
            ['game_type_id' => 2, 'amount' => 0.5, 'winning_amount' => 2250],
            ['game_type_id' => 2, 'amount' => 1, 'winning_amount' => 4500],
            ['game_type_id' => 2, 'amount' => 2, 'winning_amount' => 9000],
            ['game_type_id' => 2, 'amount' => 5, 'winning_amount' => 22500],
            ['game_type_id' => 2, 'amount' => 10, 'winning_amount' => 45000],
            ['game_type_id' => 2, 'amount' => 20, 'winning_amount' => 90000],
            ['game_type_id' => 2, 'amount' => 50, 'winning_amount' => 225000],
            ['game_type_id' => 2, 'amount' => 100, 'winning_amount' => 450000],
            ['game_type_id' => 2, 'amount' => 200, 'winning_amount' => 900000],
            ['game_type_id' => 2, 'amount' => 330, 'winning_amount' => 1485000],
            ['game_type_id' => 2, 'amount' => 500, 'winning_amount' => 2250000],
            ['game_type_id' => 2, 'amount' => 1000, 'winning_amount' => 4500000],
            ['game_type_id' => 2, 'amount' => 2000, 'winning_amount' => 9000000],
            ['game_type_id' => 2, 'amount' => 5000, 'winning_amount' => 22500000],
            ['game_type_id' => 2, 'amount' => 10000, 'winning_amount' => 45000000],
            ['game_type_id' => 2, 'amount' => 20000, 'winning_amount' => 90000000],

            // D4 (multiplier: 10000x)
            ['game_type_id' => 3, 'amount' => 0.5, 'winning_amount' => 5000],
            ['game_type_id' => 3, 'amount' => 1, 'winning_amount' => 10000],
            ['game_type_id' => 3, 'amount' => 2, 'winning_amount' => 20000],
            ['game_type_id' => 3, 'amount' => 5, 'winning_amount' => 50000],
            ['game_type_id' => 3, 'amount' => 10, 'winning_amount' => 100000],
            ['game_type_id' => 3, 'amount' => 20, 'winning_amount' => 200000],
            ['game_type_id' => 3, 'amount' => 50, 'winning_amount' => 500000],
            ['game_type_id' => 3, 'amount' => 100, 'winning_amount' => 1000000],
            ['game_type_id' => 3, 'amount' => 200, 'winning_amount' => 2000000],
            ['game_type_id' => 3, 'amount' => 330, 'winning_amount' => 3300000],
            ['game_type_id' => 3, 'amount' => 500, 'winning_amount' => 5000000],
            ['game_type_id' => 3, 'amount' => 1000, 'winning_amount' => 10000000],
            ['game_type_id' => 3, 'amount' => 2000, 'winning_amount' => 20000000],
            ['game_type_id' => 3, 'amount' => 5000, 'winning_amount' => 50000000],
            ['game_type_id' => 3, 'amount' => 10000, 'winning_amount' => 100000000],
            ['game_type_id' => 3, 'amount' => 20000, 'winning_amount' => 200000000],
        ];
        foreach ($winningAmounts as $data) {
            \App\Models\WinningAmount::updateOrCreate(
                [
                    'game_type_id' => $data['game_type_id'],
                    'amount' => $data['amount']
                ],
                ['winning_amount' => $data['winning_amount']]
            );
        }
    }
}
