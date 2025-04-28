<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\TellerSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ScheduleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        $this->call([
            UserSeeder::class,
            LocationSeeder::class,
            // Update user locations after locations are created
            ScheduleSeeder::class,
            DrawSeeder::class,
            BetSeeder::class,
            ResultSeeder::class,
            // ClaimSeeder::class // Enable if you add real claim logic
        ]);
    }
}
