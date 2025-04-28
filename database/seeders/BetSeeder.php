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
        for ($i = 1; $i <= 10; $i++) {
            Bet::create([
                'bet_number' => rand(1, 99),
                'amount' => 20,
                'schedule_id' => 1,
                'teller_id' => 3, // Teller Jane
                'location_id' => 1, // Tacurong City
                'bet_date' => today(),
                'ticket_id' => uniqid(),
                'status' => 'active',
            ]);
        }
    }
}
