<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LowWinNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example low win rules
        $lowWinNumbers = [
            // S2: all numbers, amount 2, low win 1000
            ['game_type_id' => 1, 'amount' => 2, 'bet_number' => null, 'winning_amount' => 1000, 'reason' => 'All numbers low win for S2-2'],
            // S3: specific number, amount 1, low win 2000
            ['game_type_id' => 2, 'amount' => 1, 'bet_number' => '123', 'winning_amount' => 2000, 'reason' => 'Low win for S3-1 on 123'],
            // D4: all numbers, amount 1, low win 8000
            ['game_type_id' => 3, 'amount' => 1, 'bet_number' => null, 'winning_amount' => 8000, 'reason' => 'All numbers low win for D4-1'],
        ];
        foreach ($lowWinNumbers as $data) {
            \App\Models\LowWinNumber::updateOrCreate(
                [
                    'game_type_id' => $data['game_type_id'],
                    'amount' => $data['amount'],
                    'bet_number' => $data['bet_number']
                ],
                [
                    'winning_amount' => $data['winning_amount'],
                    'reason' => $data['reason']
                ]
            );
        }
    }
}
