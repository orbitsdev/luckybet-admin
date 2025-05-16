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
            // S2
            ['game_type_id' => 1, 'amount' => 1, 'winning_amount' => 750],
            ['game_type_id' => 1, 'amount' => 2, 'winning_amount' => 1500],
            // S3
            ['game_type_id' => 2, 'amount' => 1, 'winning_amount' => 4500],
            ['game_type_id' => 2, 'amount' => 2, 'winning_amount' => 9000],
            // D4
            ['game_type_id' => 3, 'amount' => 1, 'winning_amount' => 10000],
            ['game_type_id' => 3, 'amount' => 2, 'winning_amount' => 20000],
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
