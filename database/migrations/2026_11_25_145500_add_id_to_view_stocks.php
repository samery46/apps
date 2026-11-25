<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
        CREATE OR REPLACE SQL SECURITY INVOKER VIEW view_stocks AS
        SELECT
            CONCAT(t.barang_id, '-', t.plant_id) AS id,
            t.barang_id,
            t.plant_id,
            COALESCE(t_masuk, 0) + COALESCE(sa_tambah, 0) AS total_masuk,
            COALESCE(t_keluar, 0) + COALESCE(sa_kurang, 0) AS total_keluar
        FROM (
            SELECT barang_id, plant_id FROM transaksi_in_outs
            UNION
            SELECT barang_id, plant_id FROM stock_adjustments
        ) AS t
        LEFT JOIN (
            SELECT barang_id, plant_id, SUM(qty) AS t_masuk
            FROM transaksi_in_outs
            WHERE jenis_transaksi = 'masuk' AND status_pengajuan = 'selesai'
            GROUP BY barang_id, plant_id
        ) AS tm ON tm.barang_id = t.barang_id AND tm.plant_id = t.plant_id
        LEFT JOIN (
            SELECT barang_id, plant_id, SUM(qty) AS t_keluar
            FROM transaksi_in_outs
            WHERE jenis_transaksi = 'keluar' AND status_pengajuan = 'selesai'
            GROUP BY barang_id, plant_id
        ) AS tk ON tk.barang_id = t.barang_id AND tk.plant_id = t.plant_id
        LEFT JOIN (
            SELECT barang_id, plant_id, SUM(jumlah_penyesuaian) AS sa_tambah
            FROM stock_adjustments
            WHERE tipe = 'penambahan'
            GROUP BY barang_id, plant_id
        ) AS sta ON sta.barang_id = t.barang_id AND sta.plant_id = t.plant_id
        LEFT JOIN (
            SELECT barang_id, plant_id, SUM(jumlah_penyesuaian) AS sa_kurang
            FROM stock_adjustments
            WHERE tipe = 'pengurangan'
            GROUP BY barang_id, plant_id
        ) AS stk ON stk.barang_id = t.barang_id AND stk.plant_id = t.plant_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
