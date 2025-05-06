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
        Schema::create('bet_ratio_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bet_ratio_id')->constrained('bet_ratios');
            $table->foreignId('game_type_id')->constrained('game_types');
            $table->string('number');
            $table->decimal('amount_limit', 10, 2);
            $table->time('draw_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_ratio_restrictions');
    }
};
