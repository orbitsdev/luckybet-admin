<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'phone' => '09123456789',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Location if it doesn't exist
        $location = Location::firstOrCreate(
            ['name' => 'Main Branch'],
            [
                'address' => 'Tacurong City',
                'is_active' => true,
            ]
        );

        // Create Coordinator
        $coordinator = User::create([
            'name' => 'Coordinator John',
            'username' => 'coordinatorjohn',
            'email' => 'coordinator@example.com',
            'phone' => '09123456788',
            'password' => Hash::make('password'),
            'role' => 'coordinator',
            'is_active' => true,
            'location_id' => $location->id,
        ]);

        // Create Teller with coordinator relationship
        User::create([
            'name' => 'Teller Jane',
            'username' => 'tellerjane',
            'email' => 'teller@example.com',
            'phone' => '09123456787',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'is_active' => true,
            'coordinator_id' => $coordinator->id, // Link to coordinator
            'location_id' => $location->id,
        ]);

        // Add Teller Joe
        User::create([
            'name' => 'Teller Joe',
            'username' => 'tellerjoe',
            'email' => 'joe@gmail.com',
            'phone' => '09123456786',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'is_active' => true,
            'coordinator_id' => $coordinator->id,
            'location_id' => $location->id,
        ]);

        // Add Teller Kristine
        User::create([
            'name' => 'Teller Kristine',
            'username' => 'tellerkristine',
            'email' => 'kristine@gmail.com',
            'phone' => '09123456785',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'is_active' => true,
            'coordinator_id' => $coordinator->id,
            'location_id' => $location->id,
        ]);
    }
}
