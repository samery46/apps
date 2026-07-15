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
        // Schema::create('view_stocks', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });

        DB::statement("
            CREATE VIEW `view_stocks` AS
            SELECT
                `barang_id` AS `id`,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'masuk' AND status_pengajuan = 'selesai' THEN qty ELSE 0 END), 0) AS `total_masuk`,
                COALESCE(SUM(CASE WHEN jenis_transaksi = 'keluar' AND status_pengajuan = 'selesai' THEN qty ELSE 0 END), 0) AS `total_keluar`
            FROM `transaksi_in_outs`
            WHERE `barang_id` IS NOT NULL
            GROUP BY `barang_id`
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('view_stocks');
        DB::statement("DROP VIEW IF EXISTS view_stocks");
    }
};
