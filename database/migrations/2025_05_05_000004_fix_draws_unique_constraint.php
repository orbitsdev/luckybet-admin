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
        Schema::table('draws', function (Blueprint $table) {
            // Drop the existing unique constraint
            $table->dropUnique(['draw_date', 'draw_time']);
            
            // Add a new unique constraint that includes game_type_id
            $table->unique(['draw_date', 'draw_time', 'game_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draws', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['draw_date', 'draw_time', 'game_type_id']);
            
            // Restore the original unique constraint
            $table->unique(['draw_date', 'draw_time']);
        });
    }
};
