<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\LowWinNumber;
use App\Models\GameType;
use Carbon\Carbon;

class LowWinNumberSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::firstOrCreate([
            'name' => 'Main Branch',
        ], [
            'address' => 'Test',
            'is_active' => true,
        ]);

        $today = Carbon::today();
        $nextMonth = $today->copy()->addMonth();
        $lastMonth = $today->copy()->subMonth();

        $this->call(GameTypeSeeder::class);

        $s2 = GameType::where('code', 'S2')->first();
        $s3 = GameType::where('code', 'S3')->first();
        $d4 = GameType::where('code', 'D4')->first();

        if (!$s2 || !$s3 || !$d4) {
            throw new \Exception('Game types not found. Please run GameTypeSeeder.');
        }

        $lowWinData = [
            // Global (no location_id)
            ['game_type_id' => $s2->id, 'bet_number' => '11', 'winning_amount' => 500, 'reason' => 'Global S2 control', 'is_active' => true, 'start_date' => $today, 'end_date' => $nextMonth],
            ['game_type_id' => $s3->id, 'bet_number' => '222', 'winning_amount' => 1500, 'reason' => 'Global S3 block', 'is_active' => true, 'start_date' => $today],
            ['game_type_id' => $d4->id, 'bet_number' => '4444', 'winning_amount' => 2000, 'reason' => 'Global D4 flag', 'is_active' => true, 'start_date' => $today],

            // Location-specific
            ['game_type_id' => $s2->id, 'bet_number' => '22', 'winning_amount' => 800, 'reason' => 'Old branch risk', 'is_active' => false, 'start_date' => $lastMonth, 'end_date' => $today->copy()->subDay(), 'location_id' => $location->id],
            ['game_type_id' => $s3->id, 'bet_number' => '333', 'winning_amount' => 5000, 'reason' => 'Too popular at branch', 'is_active' => true, 'start_date' => $lastMonth, 'end_date' => $nextMonth, 'location_id' => $location->id],
            ['game_type_id' => $d4->id, 'bet_number' => '5555', 'winning_amount' => 2500, 'reason' => 'Branch D4 alert', 'is_active' => true, 'start_date' => $today, 'location_id' => $location->id],
        ];

        foreach ($lowWinData as $entry) {
            $data = [
                'winning_amount' => $entry['winning_amount'],
                'reason' => $entry['reason'],
                'is_active' => $entry['is_active'] ?? true,
                'start_date' => $entry['start_date'] ?? null,
                'end_date' => $entry['end_date'] ?? null,
                'user_id' => 1,
            ];

            LowWinNumber::updateOrCreate([
                'game_type_id' => $entry['game_type_id'],
                'bet_number' => $entry['bet_number'],
                'location_id' => $entry['location_id'] ?? null, // ← supports global
            ], $data);
        }
    }
}
