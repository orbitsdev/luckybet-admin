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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 10, 2);
            $table->date('commission_date');
            $table->enum('type', ['sales', 'claims']);
            $table->foreignId('bet_id')->nullable()->constrained('bets')->nullOnDelete();
            $table->foreignId('claim_id')->nullable()->constrained('claims')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
