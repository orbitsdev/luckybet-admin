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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bet_id')->constrained();
            $table->foreignId('result_id')->constrained();
            $table->foreignId('teller_id')->constrained('users');
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending');
            $table->dateTime('claim_at')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
