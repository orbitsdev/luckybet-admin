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
        Schema::create('bet_ratios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinator_id')->constrained('users');
            $table->date('draw_date');
            $table->decimal('s2_limit', 10, 2)->nullable();
            $table->decimal('s3_limit', 10, 2)->nullable();
            $table->decimal('d4_limit', 10, 2)->nullable();
            $table->decimal('s2_win_amount', 10, 2)->nullable();
            $table->decimal('s3_win_amount', 10, 2)->nullable();
            $table->decimal('d4_win_amount', 10, 2)->nullable();
            $table->decimal('s2_low_win_amount', 10, 2)->nullable();
            $table->decimal('s3_low_win_amount', 10, 2)->nullable();
            $table->decimal('d4_low_win_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_ratios');
    }
};
