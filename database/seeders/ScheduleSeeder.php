<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create schedules if none exist
        if (Schedule::count() > 0) {
            return;
        }
        
        $schedules = [
            ['name' => '2:00 PM', 'draw_time' => '14:00:00', 'is_active' => true],
            ['name' => '5:00 PM', 'draw_time' => '17:00:00', 'is_active' => true],
            ['name' => '9:00 PM', 'draw_time' => '21:00:00', 'is_active' => true],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
