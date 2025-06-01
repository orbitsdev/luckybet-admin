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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique()->nullable(); // Assigned on finalization
            $table->unsignedBigInteger('teller_id');
            $table->unsignedBigInteger('location_id');
            $table->date('receipt_date')->nullable(); // Set on finalization
            $table->enum('status', ['draft', 'placed', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 12, 2)->nullable(); // Optionally store, can also compute
            $table->timestamps();

            $table->foreign('teller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
