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
            $table->unsignedBigInteger('bet_id');
            $table->unsignedBigInteger('result_id');
            $table->foreignId('teller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'processed', 'rejected'])->default('pending');
            $table->timestamp('claimed_at')->nullable();
            $table->string('qr_code_data')->nullable();
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
