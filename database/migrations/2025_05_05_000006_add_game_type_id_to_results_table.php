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
        Schema::table('results', function (Blueprint $table) {
            // Add the game_type_id column if it doesn't exist
            if (!Schema::hasColumn('results', 'game_type_id')) {
                $table->foreignId('game_type_id')->nullable()->constrained();
            }
        });
        
        // Use try-catch to handle the case where the unique constraint doesn't exist
        try {
            Schema::table('results', function (Blueprint $table) {
                // Drop the unique constraint on draw_date and draw_time
                $table->dropUnique(['draw_date', 'draw_time']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }
        
        // Use try-catch to handle the case where the unique constraint already exists
        try {
            Schema::table('results', function (Blueprint $table) {
                // Add a new unique constraint that includes game_type_id
                $table->unique(['draw_date', 'draw_time', 'game_type_id']);
            });
        } catch (\Exception $e) {
            // Constraint might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Drop the unique constraint that includes game_type_id
            $table->dropUnique(['draw_date', 'draw_time', 'game_type_id']);
            
            // Add back the original unique constraint
            $table->unique(['draw_date', 'draw_time']);
            
            // Drop the foreign key constraint
            $table->dropForeign(['game_type_id']);
            
            // Drop the game_type_id column
            $table->dropColumn('game_type_id');
        });
    }
};
