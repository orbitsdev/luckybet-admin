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
            $table->string('bet_number');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('draw_id');
            $table->foreignId('teller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->date('bet_date');
            $table->string('ticket_id')->unique();
            $table->enum('status', ['active', 'cancelled', 'claimed', 'won', 'lost'])->default('active');
            $table->boolean('is_combination')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bets');
    }
    
    /**
     * After all the tables are created.
     */
    public function afterUp(): void
    {
        Schema::table('bets', function (Blueprint $table) {
            $table->foreign('draw_id')->references('id')->on('draws')->cascadeOnDelete();
        });
    }
};
