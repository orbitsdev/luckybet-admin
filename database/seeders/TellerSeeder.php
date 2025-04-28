<?php

namespace Database\Seeders;

use App\Models\Teller;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tellers = [
            [
                'user_id' => 2, // teller1 (based on UserSeeder order)
                'coordinator_id' => 1, // admin user
                'commission_rate' => 5,
                'balance' => 0,
            ],
            [
                'user_id' => 3, // teller2
                'coordinator_id' => 1,
                'commission_rate' => 10,
                'balance' => 0,
            ],
        ];

        foreach ($tellers as $teller) {
            Teller::create($teller);
        }
    }
}
