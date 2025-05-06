<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create locations if none exist
        if (Location::count() > 0) {
            return;
        }
        
        // Get coordinator user
        $coordinator = User::where('role', 'coordinator')->first();
        
        $locations = [
            [
                'name' => 'Main Branch',
                'address' => 'Tacurong City',
                'is_active' => true,
            ],
            [
                'name' => 'Isulan Branch',
                'address' => 'Isulan City',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
        
        // Update users with location IDs
        if ($coordinator) {
            $mainBranch = Location::where('name', 'Main Branch')->first();
            if ($mainBranch) {
                $coordinator->location_id = $mainBranch->id;
                $coordinator->save();
                
                // Also update tellers with this location
                User::where('role', 'teller')->update(['location_id' => $mainBranch->id]);
            }
        }
    }
}
