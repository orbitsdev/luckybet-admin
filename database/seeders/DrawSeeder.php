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
        
        // Create draws for yesterday (closed with results)
        $yesterday = now()->subDay()->format('Y-m-d');
        foreach ($schedules as $schedule) {
            // Create a draw for each game type
            foreach ($gameTypes as $gameType) {
                Draw::create([
                    'draw_date' => $yesterday,
                    'draw_time' => $schedule->draw_time,
                    'game_type_id' => $gameType->id,
                    'is_open' => false, // Closed because it's in the past
                ]);
            }
        }
        
        // Create draws for today (mix of open and closed)
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');
        
        // Get all schedules in order of draw time
        $todaySchedules = Schedule::orderBy('draw_time')->get();
        
        // Create draws for each schedule for today
        foreach ($todaySchedules as $index => $schedule) {
            $scheduleTime = $schedule->draw_time;
            $isPastTime = $scheduleTime < $currentTime;
            
            // First schedule is always closed (morning draw with results)
            // Middle schedules depend on current time
            // Last schedule is always open (evening draw)
            $isOpen = false;
            if ($index === 0) {
                $isOpen = false; // Morning draw - already closed
            } elseif ($index === count($todaySchedules) - 1) {
                $isOpen = true; // Evening draw - still open
            } else {
                $isOpen = !$isPastTime; // Afternoon draws - depends on time
            }
            
            // Create a draw for each game type for this schedule
            foreach ($gameTypes as $gameType) {
                Draw::create([
                    'draw_date' => $today,
                    'draw_time' => $schedule->draw_time,
                    'game_type_id' => $gameType->id,
                    'is_open' => $isOpen,
                ]);
            }
        }
        
        // Create draws for tomorrow (all open)
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        // Get all schedules in order of draw time
        $tomorrowSchedules = Schedule::orderBy('draw_time')->get();
        
        // Create draws for each schedule for tomorrow (all open)
        foreach ($tomorrowSchedules as $schedule) {
            // Create a draw for each game type for this schedule
            foreach ($gameTypes as $gameType) {
                Draw::create([
                    'draw_date' => $tomorrow,
                    'draw_time' => $schedule->draw_time,
                    'game_type_id' => $gameType->id,
                    'is_open' => true, // All tomorrow's draws are open
                ]);
            }
        }
    }
}
