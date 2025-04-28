<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Main Branch',
                'address' => 'Tacurong City',
                'coordinator_id' => 1, // Assuming ID 1 = Default Admin
            ],
            [
                'name' => 'Isulan Branch',
                'address' => 'Isulan City',
                'coordinator_id' => 1,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
