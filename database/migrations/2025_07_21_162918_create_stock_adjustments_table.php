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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('kategoris')->cascadeOnDelete(); // karena barang disimpan di tabel kategoris
            $table->date('tanggal')->default(now());
            $table->integer('jumlah_penyesuaian');
            $table->enum('tipe', ['penambahan', 'pengurangan']); // arah koreksi stok
            $table->string('alasan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
