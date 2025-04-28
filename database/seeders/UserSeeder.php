<?php

namespace Database\Seeders;

use App\Models\User;
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

          // Coordinator Admin
         // Create Admin
         User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Coordinator
        User::create([
            'name' => 'Coordinator John',
            'username' => 'coordinatorjohn',
            'email' => 'coordinator@example.com',
            'password' => Hash::make('password'),
            'role' => 'coordinator',
            'is_active' => true,
        ]);

        // Create Teller
        User::create([
            'name' => 'Teller Jane',
            'username' => 'tellerjane',
            'email' => 'teller@example.com',
            'password' => Hash::make('password'),
            'role' => 'teller',
            'is_active' => true,
        ]);
    }
}
