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
        Schema::create('low_win_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->nullable()->constrained('draws')->onDelete('cascade');
            $table->foreignId('game_type_id')->constrained('game_types');
            $table->string('bet_number');
            $table->decimal('winning_amount', 18, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('low_win_numbers');
    }
};
