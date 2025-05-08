<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default game types if they don't exist
        if (GameType::count() === 0) {
            $gameTypes = [
                [
                    'name' => '2 Digit', 
                    'code' => 'S2', 
                    'digit_count' => 2,
                    'is_active' => true
                ],
                [
                    'name' => '3 Digit', 
                    'code' => 'S3', 
                    'digit_count' => 3,
                    'is_active' => true
                ],
                [
                    'name' => '4 Digit', 
                    'code' => 'D4', 
                    'digit_count' => 4,
                    'is_active' => true
                ],
            ];
            
            foreach ($gameTypes as $gameType) {
                GameType::create($gameType);
            }
        }
    }
}
