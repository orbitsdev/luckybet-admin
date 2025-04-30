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
        Schema::create('number_flags', function (Blueprint $table) {
            $table->id();
    $table->string('number');
    $table->foreignId('schedule_id')->constrained('schedules');
    $table->date('date');
    $table->foreignId('location_id')->constrained('locations');
    $table->enum('type', ['sold_out', 'low_win']);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_flags');
    }
};
