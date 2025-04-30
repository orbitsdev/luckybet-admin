<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\NumberFlag;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class NumberFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all locations and schedules
        $locations = Location::all();
        $schedules = Schedule::all();
        
        if ($locations->isEmpty() || $schedules->isEmpty()) {
            $this->command->info('Skipping NumberFlag seeder: No locations or schedules found.');
            return;
        }
        
        // Create some sample number flags for each location
        foreach ($locations as $location) {
            // Create some "sold out" flags
            $this->createSoldOutFlags($location, $schedules);
            
            // Create some "low win" flags
            $this->createLowWinFlags($location, $schedules);
        }
        
        $this->command->info('Number flags seeded successfully!');
    }
    
    /**
     * Create sold out flags for a location
     */
    private function createSoldOutFlags($location, $schedules): void
    {
        // Create 5 sold out flags for each location
        for ($i = 0; $i < 5; $i++) {
            // Generate a random 3-digit number
            $number = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            // Pick a random schedule
            $schedule = $schedules->random();
            
            // Create the flag for today's date
            NumberFlag::create([
                'number' => $number,
                'schedule_id' => $schedule->id,
                'date' => now()->format('Y-m-d'),
                'location_id' => $location->id,
                'type' => 'sold_out',
                'is_active' => true,
            ]);
        }
        
        // Create a few inactive sold out flags
        for ($i = 0; $i < 2; $i++) {
            $number = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $schedule = $schedules->random();
            
            NumberFlag::create([
                'number' => $number,
                'schedule_id' => $schedule->id,
                'date' => now()->format('Y-m-d'),
                'location_id' => $location->id,
                'type' => 'sold_out',
                'is_active' => false,
            ]);
        }
    }
    
    /**
     * Create low win flags for a location
     */
    private function createLowWinFlags($location, $schedules): void
    {
        // Create 3 low win flags for each location
        for ($i = 0; $i < 3; $i++) {
            // Generate a random 3-digit number
            $number = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            
            // Pick a random schedule
            $schedule = $schedules->random();
            
            // Create the flag for today's date
            NumberFlag::create([
                'number' => $number,
                'schedule_id' => $schedule->id,
                'date' => now()->format('Y-m-d'),
                'location_id' => $location->id,
                'type' => 'low_win',
                'is_active' => true,
            ]);
        }
        
        // Create a few inactive low win flags
        for ($i = 0; $i < 2; $i++) {
            $number = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $schedule = $schedules->random();
            
            NumberFlag::create([
                'number' => $number,
                'schedule_id' => $schedule->id,
                'date' => now()->format('Y-m-d'),
                'location_id' => $location->id,
                'type' => 'low_win',
                'is_active' => false,
            ]);
        }
    }
}
