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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_id')->constrained('draws');
            $table->date('draw_date')->nullable();
            $table->time('draw_time')->nullable();
            $table->string('s2_winning_number')->nullable();
            $table->string('s3_winning_number')->nullable();
            $table->string('d4_winning_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
