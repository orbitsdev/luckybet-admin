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
          User::create([
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'name' => 'Default Admin',
            'email' => 'admin@example.com',
            'phone' => '09123456789',
            'role' => 'coordinator',
            'is_active' => true,
        ]);

        // Teller 1
        User::create([
            'username' => 'teller1',
            'password' => Hash::make('password123'),
            'name' => 'John Teller',
            'email' => 'john.teller@example.com',
            'phone' => '09121231234',
            'role' => 'teller',
            'is_active' => true,
        ]);

        // Teller 2
        User::create([
            'username' => 'teller2',
            'password' => Hash::make('password123'),
            'name' => 'Jane Teller',
            'email' => 'jane.teller@example.com',
            'phone' => '09129876543',
            'role' => 'teller',
            'is_active' => true,
        ]);

        // Customer
        User::create([
            'username' => 'customer1',
            'password' => Hash::make('password123'),
            'name' => 'Sample Customer',
            'email' => 'customer@example.com',
            'phone' => '09998887766',
            'role' => 'customer',
            'is_active' => true,
        ]);
    }
}
