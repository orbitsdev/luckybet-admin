<?php

namespace Database\Seeders;

use App\Models\Draw;
use App\Models\Schedule;
use App\Models\GameType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DrawSeeder extends Seeder
{
    /**
     * Create default schedules if none exist
     */
    private function createSchedulesIfNeeded(): void
    {
        if (Schedule::count() === 0) {
            $defaultSchedules = [
                ['name' => '2:00 PM', 'draw_time' => '14:00:00', 'is_active' => true],
                ['name' => '5:00 PM', 'draw_time' => '17:00:00', 'is_active' => true],
                ['name' => '9:00 PM', 'draw_time' => '21:00:00', 'is_active' => true]
            ];

            foreach ($defaultSchedules as $schedule) {
                Schedule::create($schedule);
            }
        }
    }

    /**
     * Create default game types if none exist
     */
    private function createGameTypesIfNeeded(): void
    {
        if (GameType::count() === 0) {
            $gameTypes = [
                ['name' => '2 Digit', 'code' => 'S2', 'is_active' => true],
                ['name' => '3 Digit', 'code' => 'S3', 'is_active' => true],
                ['name' => '4 Digit', 'code' => 'D4', 'is_active' => true],
            ];

            foreach ($gameTypes as $gameType) {
                GameType::create($gameType);
            }
        }
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have schedules
        $this->createSchedulesIfNeeded();

        // Make sure we have game types
        $this->createGameTypesIfNeeded();

        // Get all schedules
        $schedules = Schedule::all();

        // Get all game types
        $gameTypes = GameType::where('is_active', true)->get();

        // Create draws for today only, using all active schedules
        $today = now()->format('Y-m-d');
        $schedules = Schedule::where('is_active', true)->orderBy('draw_time')->get();
        foreach ($schedules as $schedule) {
            Draw::create([
                'draw_date' => $today,
                'draw_time' => $schedule->draw_time,
                'is_open' => true,
            ]);
        }
    }
}
