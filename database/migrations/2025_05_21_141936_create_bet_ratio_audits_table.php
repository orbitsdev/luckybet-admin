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
        Schema::create('bet_ratio_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bet_ratio_id')->constrained('bet_ratios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who did the action
            $table->decimal('old_max_amount', 10, 2)->nullable();
            $table->decimal('new_max_amount', 10, 2);
            $table->string('action'); // e.g., 'set', 'update', 'delete'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bet_ratio_audits');
    }
};
