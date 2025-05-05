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
        // First, create a game_types table
        Schema::create('game_types', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // S2, S3, D4
            $table->string('name'); // Swertres 2D, Swertres 3D, Digit 4
            $table->integer('digits'); // 2, 3, 4
            $table->timestamps();
        });

        // Now, modify the draws table to remove the type and link to schedule and game_type
        Schema::table('draws', function (Blueprint $table) {
            // Drop the type column
            $table->dropColumn('type');
            
            // Add foreign keys to schedule and game_type
            $table->foreignId('schedule_id')->constrained();
            $table->foreignId('game_type_id')->constrained();
            
            // Add a unique constraint to prevent duplicates
            $table->unique(['draw_date', 'schedule_id', 'game_type_id']);
        });

        // Update the results table to match
        Schema::table('results', function (Blueprint $table) {
            // Remove the type column if it exists
            if (Schema::hasColumn('results', 'type')) {
                $table->dropColumn('type');
            }
            
            // Add game_type_id if it doesn't exist
            if (!Schema::hasColumn('results', 'game_type_id')) {
                $table->foreignId('game_type_id')->constrained();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert results table changes
        Schema::table('results', function (Blueprint $table) {
            if (Schema::hasColumn('results', 'game_type_id')) {
                $table->dropForeign(['game_type_id']);
                $table->dropColumn('game_type_id');
            }
            $table->enum('type', ['S2', 'S3', 'D4'])->nullable();
        });

        // Revert draws table changes
        Schema::table('draws', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['game_type_id']);
            $table->dropColumn(['schedule_id', 'game_type_id']);
            $table->enum('type', ['S2', 'S3', 'D4']);
        });

        // Drop the game_types table
        Schema::dropIfExists('game_types');
    }
};
