<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create the game_types table if it doesn't exist
        if (!Schema::hasTable('game_types')) {
            Schema::create('game_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 5)->unique(); // S2, S3, D4, etc.
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            
            // Seed the default game types
            $this->seedGameTypes();
        }
        
        // Add game_type column to bets table if it doesn't exist
        if (!Schema::hasColumn('bets', 'game_type')) {
            Schema::table('bets', function (Blueprint $table) {
                $table->string('game_type', 5)->nullable()->after('draw_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_types');
    }
    
    /**
     * Seed the default game types
     */
    private function seedGameTypes(): void
    {
        $gameTypes = [
            [
                'name' => 'Swertres 2-Digit',
                'code' => 'S2',
                'description' => 'A 2-digit lottery game',
                'is_active' => true,
            ],
            [
                'name' => 'Swertres 3-Digit',
                'code' => 'S3',
                'description' => 'A 3-digit lottery game',
                'is_active' => true,
            ],
            [
                'name' => 'Digit 4',
                'code' => 'D4',
                'description' => 'A 4-digit lottery game',
                'is_active' => true,
            ],
        ];
        
        foreach ($gameTypes as $gameType) {
            DB::table('game_types')->insert($gameType);
        }
    }
};
