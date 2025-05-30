<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\Draw;
use App\Models\User;
use App\Models\Result;
use App\Models\GameType;
use App\Models\Location;
use App\Models\BetRatio;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReportTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set the test date to today
        $testDate = Carbon::today();
        
        // Get game types
        $s2GameType = GameType::where('code', 'S2')->first();
        $s3GameType = GameType::where('code', 'S3')->first();
        $d4GameType = GameType::where('code', 'D4')->first();
        
        if (!$s2GameType || !$s3GameType || !$d4GameType) {
            $this->command->error('Game types not found. Please run GameTypeSeeder first.');
            return;
        }
        
        // Get locations (for assigning to bets)
        $location = Location::first();
        
        if (!$location) {
            $this->command->error('No locations found. Please run LocationSeeder first.');
            return;
        }
        
        // Get coordinators first
        $coordinator = User::where('role', 'coordinator')->first();
        
        if (!$coordinator) {
            $this->command->error('No coordinator found. Please run UserSeeder first.');
            return;
        }
        
        // Get tellers that belong to a coordinator
        $tellers = User::where('role', 'teller')
            ->whereNotNull('coordinator_id')
            ->take(3)
            ->get();
        
        // If no tellers with coordinators found, assign some tellers to our coordinator
        if ($tellers->count() === 0) {
            $this->command->info('No tellers with coordinators found. Assigning tellers to a coordinator...');
            
            // Get tellers without coordinators
            $tellersWithoutCoordinator = User::where('role', 'teller')
                ->whereNull('coordinator_id')
                ->take(3)
                ->get();
                
            if ($tellersWithoutCoordinator->count() === 0) {
                $this->command->error('No tellers found. Please run UserSeeder first.');
                return;
            }
            
            // Assign them to the coordinator
            foreach ($tellersWithoutCoordinator as $teller) {
                $teller->update(['coordinator_id' => $coordinator->id]);
            }
            
            // Refresh the collection
            $tellers = User::where('role', 'teller')
                ->where('coordinator_id', $coordinator->id)
                ->take(3)
                ->get();
        }
        
        // Create draws for the test date (2PM, 5PM, 9PM)
        $draws = [
            ['time' => '14:00:00', 'formatted' => '2:00 PM'],
            ['time' => '17:00:00', 'formatted' => '5:00 PM'],
            ['time' => '21:00:00', 'formatted' => '9:00 PM'],
        ];
        
        $createdDraws = [];
        
        foreach ($draws as $draw) {
            $createdDraw = Draw::create([
                'draw_date' => $testDate->format('Y-m-d'),
                'draw_time' => $draw['time'],
                'is_open' => false, // Set to false since we're creating past draws with results
            ]);
            
            $createdDraws[] = $createdDraw;
            
            // Create results for each draw
            Result::create([
                'draw_id' => $createdDraw->id,
                'draw_date' => $testDate->format('Y-m-d'),
                'draw_time' => $draw['time'],
                's2_winning_number' => str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT),
                's3_winning_number' => str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                'd4_winning_number' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            ]);
        }
        
        // Create bets for each teller across all draws and game types
        foreach ($tellers as $teller) {
            foreach ($createdDraws as $draw) {
                // Get the result for this draw
                $result = $draw->result;
                
                // Create S2 bets (some winning, some losing)
                $this->createBets(
                    $teller->id, 
                    $location->id,
                    $draw, 
                    $s2GameType, 
                    $testDate, 
                    15, // Number of bets
                    10, // Min amount
                    50, // Max amount
                    $result->s2_winning_number, // Winning number
                    2 // Number of winning bets
                );
                
                // Create S3 bets (some winning, some losing)
                $this->createBets(
                    $teller->id,
                    $location->id,
                    $draw, 
                    $s3GameType, 
                    $testDate, 
                    10, // Number of bets
                    20, // Min amount
                    100, // Max amount
                    $result->s3_winning_number, // Winning number
                    1 // Number of winning bets
                );
                
                // Create D4 bets (some winning, some losing)
                $this->createBets(
                    $teller->id,
                    $location->id,
                    $draw, 
                    $d4GameType, 
                    $testDate, 
                    8, // Number of bets
                    50, // Min amount
                    200, // Max amount
                    $result->d4_winning_number, // Winning number
                    1, // Number of winning bets
                    true // Include D4 sub-selections
                );
            }
        }
        
        // Create sold out numbers for testing
        $this->createSoldOutNumbers($createdDraws, $s2GameType, $s3GameType, $d4GameType, $location);
        
        $this->command->info('Report test data created successfully for ' . $testDate->format('F d, Y'));
    }
    
    /**
     * Create bets for a specific teller, draw, and game type
     */
    private function createBets(
        int $tellerId, 
        int $locationId,
        Draw $draw, 
        GameType $gameType, 
        Carbon $date, 
        int $count, 
        int $minAmount, 
        int $maxAmount, 
        string $winningNumber, 
        int $winningBetsCount,
        bool $includeD4SubSelection = false
    ): void {
        // Create winning bets
        for ($i = 0; $i < $winningBetsCount; $i++) {
            $amount = rand($minAmount, $maxAmount);
            $multiplier = $this->getMultiplier($gameType->code);
            $winningAmount = $amount * $multiplier;
            
            $d4SubSelection = null;
            $betNumber = $winningNumber;
            
            // For D4 bets with sub-selection
            if ($gameType->code === 'D4' && $includeD4SubSelection && $i === 0) {
                $d4SubSelection = 'S2';
                // For D4-S2, we only need to match the last 2 digits
                $betNumber = substr($winningNumber, -2);
            } elseif ($gameType->code === 'D4' && $includeD4SubSelection && $i === 1) {
                $d4SubSelection = 'S3';
                // For D4-S3, we only need to match the last 3 digits
                $betNumber = substr($winningNumber, -3);
            }
            
            Bet::create([
                'bet_number' => $betNumber,
                'amount' => $amount,
                'winning_amount' => $winningAmount,
                'draw_id' => $draw->id,
                'game_type_id' => $gameType->id,
                'teller_id' => $tellerId,
                'location_id' => $locationId,
                'bet_date' => $date->format('Y-m-d'),
                'ticket_id' => 'TICKET-' . uniqid(),
                'is_claimed' => true,
                'is_rejected' => false,
                'is_combination' => false,
                'd4_sub_selection' => $d4SubSelection,
                'commission_rate' => 0.10,
                'commission_amount' => $amount * 0.10
            ]);
        }
        
        // Create losing bets
        for ($i = 0; $i < $count - $winningBetsCount; $i++) {
            $amount = rand($minAmount, $maxAmount);
            
            // Generate a random bet number that is not the winning number
            do {
                if ($gameType->code === 'S2') {
                    $betNumber = str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
                } elseif ($gameType->code === 'S3') {
                    $betNumber = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                } else {
                    $betNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }
            } while ($betNumber === $winningNumber);
            
            $d4SubSelection = null;
            
            // For some D4 bets, add sub-selection
            if ($gameType->code === 'D4' && $includeD4SubSelection && rand(1, 3) === 1) {
                $d4SubSelection = rand(0, 1) ? 'S2' : 'S3';
            }
            
            Bet::create([
                'bet_number' => $betNumber,
                'amount' => $amount,
                'winning_amount' => 0, // Losing bet
                'draw_id' => $draw->id,
                'game_type_id' => $gameType->id,
                'teller_id' => $tellerId,
                'location_id' => $locationId,
                'bet_date' => $date->format('Y-m-d'),
                'ticket_id' => 'TICKET-' . uniqid(),
                'is_claimed' => false,
                'is_rejected' => false,
                'is_combination' => false,
                'd4_sub_selection' => $d4SubSelection,
                'commission_rate' => 0.10,
                'commission_amount' => $amount * 0.10
            ]);
        }
    }
    
    /**
     * Get the multiplier for calculating winning amounts based on game type
     */
    private function getMultiplier(string $gameTypeCode): int
    {
        switch ($gameTypeCode) {
            case 'S2':
                return 70; // 70x for S2
            case 'S3':
                return 500; // 500x for S3
            case 'D4':
                return 3500; // 3500x for D4
            default:
                return 1;
        }
    }
    
    /**
     * Create sold out numbers for testing
     */
    private function createSoldOutNumbers(
        array $draws,
        GameType $s2GameType,
        GameType $s3GameType,
        GameType $d4GameType,
        Location $location
    ): void {
        // Define popular numbers to mark as sold out
        $soldOutNumbers = [
            // S2 sold out numbers (popular numbers)
            ['game_type' => $s2GameType, 'numbers' => ['11', '22', '33', '44', '55', '66', '77', '88', '99']],
            
            // S3 sold out numbers (popular numbers)
            ['game_type' => $s3GameType, 'numbers' => ['111', '222', '333', '444', '555', '666', '777', '888', '999']],
            
            // D4 sold out numbers (popular numbers)
            ['game_type' => $d4GameType, 'numbers' => ['1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999']],
        ];
        
        $totalCreated = 0;
        
        // Get the first admin user for the user_id field
        $adminUser = User::where('role', 'admin')->first();
        
        if (!$adminUser) {
            $this->command->error('No admin user found for sold out numbers. Using first user instead.');
            $adminUser = User::first();
            
            if (!$adminUser) {
                $this->command->error('No users found. Skipping sold out numbers creation.');
                return;
            }
        }
        
        // Create sold out numbers for each draw
        foreach ($draws as $draw) {
            foreach ($soldOutNumbers as $soldOutGroup) {
                $gameType = $soldOutGroup['game_type'];
                
                foreach ($soldOutGroup['numbers'] as $number) {
                    // Check if this number is already marked as sold out
                    $existingRatio = BetRatio::where('draw_id', $draw->id)
                        ->where('game_type_id', $gameType->id)
                        ->where('location_id', $location->id)
                        ->where('bet_number', $number)
                        ->first();
                    
                    if ($existingRatio) {
                        // Update existing ratio to mark as sold out
                        $existingRatio->update(['max_amount' => 0]);
                        $this->command->info("Updated existing bet ratio for {$gameType->name} number {$number} to sold out");
                    } else {
                        // Create new sold out number
                        BetRatio::create([
                            'draw_id' => $draw->id,
                            'game_type_id' => $gameType->id,
                            'location_id' => $location->id,
                            'bet_number' => $number,
                            'max_amount' => 0, // Mark as sold out
                            'user_id' => $adminUser->id,
                        ]);
                        
                        $totalCreated++;
                    }
                }
            }
        }
        
        $this->command->info("Created {$totalCreated} sold out numbers for testing");
    }
}
