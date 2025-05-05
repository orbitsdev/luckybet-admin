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
        // First, modify the draws table to remove the type column
        // and make it represent just a draw time
        Schema::table('draws', function (Blueprint $table) {
            // Drop the type column if it exists
            if (Schema::hasColumn('draws', 'type')) {
                $table->dropColumn('type');
            }
            
            // Make sure it has a schedule_id
            if (!Schema::hasColumn('draws', 'schedule_id')) {
                $table->foreignId('schedule_id')->nullable()->constrained();
            }
            
            // Ensure unique draw dates and times
            $table->unique(['draw_date', 'draw_time']);
        });

        // Now, modify the results table to have columns for each game type
        Schema::table('results', function (Blueprint $table) {
            // Remove the winning_number column
            if (Schema::hasColumn('results', 'winning_number')) {
                $table->dropColumn('winning_number');
            }
            
            // Add separate columns for each game type's winning number
            $table->string('s2_winning_number')->nullable();
            $table->string('s3_winning_number')->nullable();
            $table->string('d4_winning_number')->nullable();
            
            // Add draw_time if not present
            if (!Schema::hasColumn('results', 'draw_time')) {
                $table->time('draw_time');
            }
            
            // Ensure unique draw dates and times for results
            $table->unique(['draw_date', 'draw_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert results table changes
        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['s2_winning_number', 's3_winning_number', 'd4_winning_number']);
            $table->string('winning_number')->nullable();
            
            if (Schema::hasColumn('results', 'draw_time')) {
                $table->dropColumn('draw_time');
            }
            
            // Drop the unique constraint
            $table->dropUnique(['draw_date', 'draw_time']);
        });

        // Revert draws table changes
        Schema::table('draws', function (Blueprint $table) {
            $table->enum('type', ['S2', 'S3', 'D4'])->nullable();
            
            // Drop the unique constraint
            $table->dropUnique(['draw_date', 'draw_time']);
            
            if (Schema::hasColumn('draws', 'schedule_id') && 
                !Schema::hasColumn('draws', 'schedule_id_foreign')) {
                $table->dropForeign(['schedule_id']);
                $table->dropColumn('schedule_id');
            }
        });
    }
};
