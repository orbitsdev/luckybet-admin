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
        Schema::table('bets', function (Blueprint $table) {
            $table->decimal('commission_rate', 5, 4)->nullable()->after('amount');    // e.g., 0.1500 for 15%
            $table->decimal('commission_amount', 10, 2)->nullable()->after('commission_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table) {
             $table->dropColumn('commission_rate');
            $table->dropColumn('commission_amount');
        });
    }
};
