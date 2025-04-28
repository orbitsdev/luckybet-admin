<?php

namespace Database\Seeders;

use App\Models\Draw;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DrawSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Draw::create([
            'draw_date' => today(),
            'draw_time' => '14:00:00',
            'type' => 'S2',
            'is_open' => true,
        ]);
    }
}
