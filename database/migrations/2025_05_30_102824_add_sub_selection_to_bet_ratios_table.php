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
        Schema::table('bet_ratios', function (Blueprint $table) {
            $table->string('sub_selection')->nullable()->after('bet_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bet_ratios', function (Blueprint $table) {
            $table->dropColumn('sub_selection');
        });
    }
};
