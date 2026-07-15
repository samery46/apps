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
        Schema::table('kategoris', function (Blueprint $table) {
            $table->boolean('is_barang')->default(false)->after('kategori_id');
            $table->string('satuan')->nullable()->after('is_barang');
            $table->integer('stok_minimum')->nullable()->after('satuan');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            //
            $table->dropColumn(['is_barang', 'satuan', 'stok_minimum']);
        });

    }
};
