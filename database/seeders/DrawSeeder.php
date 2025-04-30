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
        // Get all schedules
        $schedules = \App\Models\Schedule::all();
        if ($schedules->isEmpty()) {
            // Fallback if schedules don't exist
            $schedules = [['draw_time' => '14:00:00']];
        }
        
        // Create draws for yesterday (closed with results)
        $yesterday = now()->subDay()->format('Y-m-d');
        foreach ($schedules as $schedule) {
            Draw::create([
                'draw_date' => $yesterday,
                'draw_time' => $schedule->draw_time ?? '14:00:00',
                'type' => 'S3',
                'is_open' => false, // Closed because it's in the past
            ]);
        }
        
        // Create draws for today (mix of open and closed)
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');
        
        // Morning draw (S2) - already closed with results
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '11:00:00',
            'type' => 'S2',
            'is_open' => false,
        ]);
        
        // Afternoon draw (S3) - open or closed depending on current time
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '14:00:00',
            'type' => 'S3',
            'is_open' => '14:00:00' > $currentTime,
        ]);
        
        // Evening draw (D4) - still open
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '19:00:00',
            'type' => 'D4',
            'is_open' => true,
        ]);
        
        // Create draws for tomorrow (all open)
        $tomorrow = now()->addDay()->format('Y-m-d');
        $drawTypes = ['S2', 'S3', 'D4'];
        
        foreach ($schedules as $index => $schedule) {
            Draw::create([
                'draw_date' => $tomorrow,
                'draw_time' => $schedule->draw_time ?? '14:00:00',
                'type' => $drawTypes[$index % count($drawTypes)],
                'is_open' => true,
            ]);
        }
    }
}
