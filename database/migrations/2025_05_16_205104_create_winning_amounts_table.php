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
        Schema::create('winning_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_type_id')->constrained('game_types');
            $table->decimal('amount', 10, 2);
            $table->decimal('winning_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winning_amounts');
    }
};
