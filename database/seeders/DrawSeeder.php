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
     * Run the database seeds.
     */
    /**
     * Create default schedules if none exist
     */
    private function createSchedulesIfNeeded(): void
    {
        if (Schedule::count() === 0) {
            $defaultSchedules = [
                ['name' => 'Morning', 'draw_time' => '11:00:00'],
                ['name' => 'Afternoon', 'draw_time' => '14:00:00'],
                ['name' => 'Evening', 'draw_time' => '19:00:00']
            ];
            
            foreach ($defaultSchedules as $schedule) {
                Schedule::create($schedule);
            }
        }
    }
    
    public function run(): void
    {
        // Make sure we have schedules
        $this->createSchedulesIfNeeded();
        
        // Get all schedules
        $schedules = \App\Models\Schedule::all();
        
        // Get all game types
        $gameTypes = \App\Models\GameType::all();
        if ($gameTypes->isEmpty()) {
            // Create default game types if they don't exist
            $s2 = \App\Models\GameType::create(['code' => 'S2', 'name' => 'Swertres 2-Digit', 'digits' => 2]);
            $s3 = \App\Models\GameType::create(['code' => 'S3', 'name' => 'Swertres 3-Digit', 'digits' => 3]);
            $d4 = \App\Models\GameType::create(['code' => 'D4', 'name' => 'Digit 4', 'digits' => 4]);
            
            // Refresh the collection after creating
            $gameTypes = \App\Models\GameType::all();
        }
        
        // Default game type (S3)
        $defaultGameType = $gameTypes->where('code', 'S3')->first() ?? $gameTypes->first();
        
        // Create draws for yesterday (closed with results)
        $yesterday = now()->subDay()->format('Y-m-d');
        foreach ($schedules as $schedule) {
            // Create a draw for each game type
            foreach ($gameTypes as $gameType) {
                Draw::create([
                    'draw_date' => $yesterday,
                    'draw_time' => $schedule->draw_time ?? '14:00:00',
                    'schedule_id' => $schedule->id ?? null,
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
                    'draw_time' => $scheduleTime,
                    'schedule_id' => $schedule->id,
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
                    'schedule_id' => $schedule->id,
                    'game_type_id' => $gameType->id,
                    'is_open' => true, // All tomorrow's draws are open
                ]);
            }
        }
    }
}
