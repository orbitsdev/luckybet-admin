<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TellerSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ScheduleSeeder;
use Database\Seeders\GameTypeSeeder;
use Database\Seeders\TallySheetSeeder;
use Database\Seeders\BettingTestSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in the correct order to maintain data integrity
        $this->call([
            // 1. Create users first (admin, coordinators, tellers)
            UserSeeder::class,
            
            // 2. Create locations and assign coordinators
            LocationSeeder::class,
            
            // 3. Create game types for multi-game lottery system
            GameTypeSeeder::class,
            
            // 4. Create schedules for draws
            ScheduleSeeder::class,
            
            // 5. Create draws (past, current, and future)
            DrawSeeder::class,
            
            // 6. Create bets for all draws
            BetSeeder::class,
            
            // 7. Create results for closed draws and update bet statuses
            ResultSeeder::class,
            
            // 8. Create claims for winning bets
            ClaimSeeder::class,
            
            // 9. Generate tally sheets based on all the above data
            TallySheetSeeder::class,
            
            // 10. Create number flags for risk management
            NumberFlagSeeder::class,
        ]);
    }
}
