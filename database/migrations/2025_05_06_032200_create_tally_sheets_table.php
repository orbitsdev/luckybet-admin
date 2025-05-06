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
        Schema::create('tally_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')->constrained('users');
            $table->foreignId('location_id')->constrained();
            $table->date('sheet_date');
            $table->decimal('total_sales', 12, 2);
            $table->decimal('total_claims', 12, 2);
            $table->decimal('total_commission', 10, 2);
            $table->decimal('net_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tally_sheets');
    }
};
