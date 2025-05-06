<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Draw;
use App\Models\GameType;
use App\Models\Location;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BettingTestSeeder extends Seeder
{
    /**
     * Run the database seeds specifically for testing the betting functionality.
     * This creates minimal data needed to test the multi-game lottery system.
     */
    public function run(): void
    {
        // Clear existing test data to avoid duplicates
        $this->clearExistingTestData();
        
        // 1. Create a test location
        $location = Location::create([
            'name' => 'Test Branch',
            'address' => 'Test City',
            'is_active' => Schema::hasColumn('locations', 'is_active') ? true : null,
        ]);

        // 2. Create test users (admin, coordinator, teller)
        $admin = User::create([
            'name' => 'Test Admin',
            'username' => 'testadmin',
            'email' => 'testadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => Schema::hasColumn('users', 'is_active') ? true : null,
        ]);

        $coordinator = User::create([
            'name' => 'Test Coordinator',
            'username' => 'testcoordinator',
            'email' => 'testcoordinator@example.com',
            'password' => Hash::make('password'),
            'role' => 'coordinator',
            'location_id' => $location->id,
            'is_active' => Schema::hasColumn('users', 'is_active') ? true : null,
        ]);

        $teller = User::create([
            'name' => 'Test Teller',
            'username' => 'testteller',
            'email' => 'testteller@example.com',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'location_id' => $location->id,
            'is_active' => Schema::hasColumn('users', 'is_active') ? true : null,
        ]);

        // 3. Create game types (S2, S3, D4)
        $this->createGameTypes();
        
        // Get all game types
        $gameTypes = GameType::all();

        // 4. Create schedules
        $this->createSchedules();
        
        // Get all schedules
        $schedules = Schedule::all();

        // 5. Create draws for today for all game types and schedules
        $this->createDraws($schedules, $gameTypes);

        // Output confirmation
        $this->outputSuccessMessage();
    }
    
    /**
     * Clear existing test data to avoid duplicates
     */
    private function clearExistingTestData(): void
    {
        // Remove test users if they exist
        User::where('email', 'testadmin@example.com')->delete();
        User::where('email', 'testcoordinator@example.com')->delete();
        User::where('email', 'testteller@example.com')->delete();
        
        // Remove test location
        Location::where('name', 'Test Branch')->delete();
    }
    
    /**
     * Create game types with proper error handling
     */
    private function createGameTypes(): void
    {
        // Only create if none exist
        if (GameType::count() === 0) {
            $gameTypes = [
                ['name' => '2 Digit', 'code' => 'S2', 'is_active' => true],
                ['name' => '3 Digit', 'code' => 'S3', 'is_active' => true],
                ['name' => '4 Digit', 'code' => 'D4', 'is_active' => true],
            ];
            
            foreach ($gameTypes as $gameTypeData) {
                try {
                    GameType::create($gameTypeData);
                } catch (\Exception $e) {
                    // Log error but continue
                    echo "Error creating game type {$gameTypeData['code']}: {$e->getMessage()}\n";
                }
            }
        }
    }
    
    /**
     * Create schedules with proper error handling
     */
    private function createSchedules(): void
    {
        // Only create if none exist
        if (Schedule::count() === 0) {
            $schedules = [
                ['name' => '2:00 PM', 'draw_time' => '14:00:00'],
                ['name' => '5:00 PM', 'draw_time' => '17:00:00'],
                ['name' => '9:00 PM', 'draw_time' => '21:00:00'],
            ];
            
            // Check for is_active column
            $hasIsActive = Schema::hasColumn('schedules', 'is_active');
            
            foreach ($schedules as $scheduleData) {
                // Add is_active if column exists
                if ($hasIsActive) {
                    $scheduleData['is_active'] = true;
                }
                
                try {
                    Schedule::create($scheduleData);
                } catch (\Exception $e) {
                    // Log error but continue
                    echo "Error creating schedule {$scheduleData['name']}: {$e->getMessage()}\n";
                }
            }
        }
    }
    
    /**
     * Create draws for today
     */
    private function createDraws($schedules, $gameTypes): void
    {
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('H:i:s');
        
        foreach ($schedules as $schedule) {
            $isOpen = $schedule->draw_time > $currentTime;
            
            foreach ($gameTypes as $gameType) {
                try {
                    Draw::create([
                        'draw_date' => $today,
                        'schedule_id' => $schedule->id,
                        'game_type_id' => $gameType->id,
                        'is_open' => $isOpen,
                    ]);
                } catch (\Exception $e) {
                    // Log error but continue
                    echo "Error creating draw for {$gameType->code} at {$schedule->name}: {$e->getMessage()}\n";
                }
            }
        }
    }
    
    /**
     * Output success message
     */
    private function outputSuccessMessage(): void
    {
        echo "\nBetting test data seeded successfully!\n";
        echo "Test accounts:\n";
        echo "- Admin: testadmin@example.com / password\n";
        echo "- Coordinator: testcoordinator@example.com / password\n";
        echo "- Teller: testteller@example.com / password\n";
    }
}
