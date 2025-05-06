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
        Schema::create('sold_out_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinator_id')->constrained('users');
            $table->date('draw_date');
            $table->time('draw_time');
            $table->foreignId('game_type_id')->constrained('game_types');
            $table->string('bet_number');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sold_out_numbers');
    }
};
