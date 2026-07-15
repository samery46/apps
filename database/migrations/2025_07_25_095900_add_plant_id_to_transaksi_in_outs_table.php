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
        Schema::table('transaksi_in_outs', function (Blueprint $table) {
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_in_outs', function (Blueprint $table) {
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
        });
    }
};
