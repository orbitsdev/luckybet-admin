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
        Schema::create('bets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->constrained();
            $table->foreignId('game_type_id')->constrained();
            $table->foreignId('teller_id')->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('users');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->string('ticket_id')->unique();
            $table->string('bet_number');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['active', 'cancelled', 'won', 'lost', 'claimed'])->default('active');
            $table->boolean('is_combination')->default(false);
            $table->dateTime('bet_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
};
