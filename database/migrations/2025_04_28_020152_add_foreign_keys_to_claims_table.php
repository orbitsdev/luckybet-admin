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
        Schema::table('claims', function (Blueprint $table) {
            $table->foreign('bet_id')->references('id')->on('bets')->cascadeOnDelete();
            $table->foreign('result_id')->references('id')->on('results')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropForeign(['bet_id']);
            $table->dropForeign(['result_id']);
        });
    }
};
