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
                'schedule_id' => $schedule->id ?? null,
                'is_open' => false, // Closed because it's in the past
            ]);
        }
        
        // Create draws for today (mix of open and closed)
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');
        
        // Get the schedules for the day
        $morningSchedule = \App\Models\Schedule::where('draw_time', 'like', '11:%')->first();
        $afternoonSchedule = \App\Models\Schedule::where('draw_time', 'like', '14:%')->first();
        $eveningSchedule = \App\Models\Schedule::where('draw_time', 'like', '19:%')->first();
        
        // Morning draw - already closed with results
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '11:00:00',
            'schedule_id' => $morningSchedule ? $morningSchedule->id : null,
            'is_open' => false,
        ]);
        
        // Afternoon draw - open or closed depending on current time
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '14:00:00',
            'schedule_id' => $afternoonSchedule ? $afternoonSchedule->id : null,
            'is_open' => '14:00:00' > $currentTime,
        ]);
        
        // Evening draw - still open
        Draw::create([
            'draw_date' => $today,
            'draw_time' => '19:00:00',
            'schedule_id' => $eveningSchedule ? $eveningSchedule->id : null,
            'is_open' => true,
        ]);
        
        // Create draws for tomorrow (all open)
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        foreach ($schedules as $schedule) {
            Draw::create([
                'draw_date' => $tomorrow,
                'draw_time' => $schedule->draw_time ?? '14:00:00',
                'schedule_id' => $schedule->id ?? null,
                'is_open' => true,
            ]);
        }
    }
}
