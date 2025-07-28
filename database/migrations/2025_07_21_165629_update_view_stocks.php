<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            DB::statement("
            CREATE OR REPLACE VIEW view_stocks AS
            SELECT
                k.id AS id,
                k.nama AS nama_barang,
                COALESCE((
                    SELECT SUM(qty) FROM transaksi_in_outs
                    WHERE barang_id = k.id AND jenis_transaksi = 'masuk' AND status_pengajuan = 'selesai'
                ), 0)
                + COALESCE((
                    SELECT SUM(jumlah_penyesuaian) FROM stock_adjustments
                    WHERE barang_id = k.id AND tipe = 'penambahan'
                ), 0) AS total_masuk,

                COALESCE((
                    SELECT SUM(qty) FROM transaksi_in_outs
                    WHERE barang_id = k.id AND jenis_transaksi = 'keluar' AND status_pengajuan = 'selesai'
                ), 0)
                + COALESCE((
                    SELECT SUM(jumlah_penyesuaian) FROM stock_adjustments
                    WHERE barang_id = k.id AND tipe = 'pengurangan'
                ), 0) AS total_keluar
            FROM kategoris k
            WHERE k.is_barang = true
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_stocks");
    }
};
