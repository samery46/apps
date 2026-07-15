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
        Schema::create('transaksi_in_outs', function (Blueprint $table) {
            $table->id();
            // Mengacu ke entitas barang di tabel kategoris
            $table->foreignId('barang_id')->constrained('kategoris')->cascadeOnDelete();
            $table->enum('jenis_transaksi', ['Masuk', 'Keluar']);
            $table->unsignedInteger('qty');
            $table->foreignId('karyawan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('tgl_pengajuan')->nullable();
            $table->date('tgl_diterima')->nullable();
            $table->enum('status_pengajuan', ['Diajukan', 'Disetujui', 'Diproses', 'Selesai', 'Ditolak'])->default('diajukan');
            $table->enum('kondisi_barang', ['Bagus', 'Rusak'])->nullable();
            $table->boolean('pengaruh_stok')->default(false); // Bisa dikalkulasi otomatis
            $table->text('catatan')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_in_outs');
    }
};
