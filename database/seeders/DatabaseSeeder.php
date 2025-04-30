<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TellerSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ScheduleSeeder;
use Database\Seeders\TallySheetSeeder;

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
            
            // 3. Create schedules for draws
            ScheduleSeeder::class,
            
            // 4. Create draws (past, current, and future)
            DrawSeeder::class,
            
            // 5. Create bets for all draws
            BetSeeder::class,
            
            // 6. Create results for closed draws and update bet statuses
            ResultSeeder::class,
            
            // 7. Create claims for winning bets
            ClaimSeeder::class,
            
            // 8. Generate tally sheets based on all the above data
            TallySheetSeeder::class,
            
            // 9. Create number flags for risk management
            NumberFlagSeeder::class,
        ]);
    }
}
