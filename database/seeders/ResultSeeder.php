<?php

namespace Database\Seeders;

use App\Models\Result;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Result::create([
            'winning_number' => '21',
            'schedule_id' => 1,
            'draw_date' => today(),
            'coordinator_id' => 2,
        ]);
    }
}
