<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\BettingTestSeeder;

class SeedBettingTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:betting-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with minimal data needed to test the betting functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding database with betting test data...');
        
        // Run the betting test seeder
        $this->call('db:seed', [
            '--class' => BettingTestSeeder::class,
        ]);
        
        $this->info('Betting test data seeded successfully!');
        $this->info('Test accounts:');
        $this->info('- Admin: testadmin@example.com / password');
        $this->info('- Coordinator: testcoordinator@example.com / password');
        $this->info('- Teller: testteller@example.com / password');
        
        return Command::SUCCESS;
    }
}
