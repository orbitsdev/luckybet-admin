<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            // Drop the game_type column if it exists
            if (Schema::hasColumn('bets', 'game_type')) {
                $table->dropColumn('game_type');
            }
            
            // Add the game_type_id column if it doesn't exist
            if (!Schema::hasColumn('bets', 'game_type_id')) {
                $table->foreignId('game_type_id')->nullable()->constrained();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['game_type_id']);
            
            // Drop the game_type_id column
            $table->dropColumn('game_type_id');
            
            // Add back the original game_type column
            $table->string('game_type')->nullable();
        });
    }
};
