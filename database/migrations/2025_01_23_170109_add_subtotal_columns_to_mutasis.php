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
        Schema::table('mutasis', function (Blueprint $table) {
            // Menambahkan kolom subtotal1, subtotal2, subtotal3, dan grandtotal
            $table->integer('subtotal1')->nullable();
            $table->integer('subtotal2')->nullable();
            $table->integer('subtotal3')->nullable();
            $table->integer('grandtotal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasis', function (Blueprint $table) {
            $table->dropColumn('subtotal1');
            $table->dropColumn('subtotal2');
            $table->dropColumn('subtotal3');
            $table->dropColumn('grandtotal');
        });
    }
};
