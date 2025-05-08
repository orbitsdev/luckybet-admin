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
            // Drop the existing foreign key
            $table->dropForeign(['draw_id']);
            
            // Add the foreign key with cascade delete
            $table->foreign('draw_id')
                  ->references('id')
                  ->on('draws')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Drop the cascade foreign key
            $table->dropForeign(['draw_id']);
            
            // Restore the original foreign key without cascade
            $table->foreign('draw_id')
                  ->references('id')
                  ->on('draws');
        });
    }
};
