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
            $table->unsignedBigInteger('iap')->change();
            $table->unsignedBigInteger('adm')->change();
            $table->unsignedBigInteger('potongan')->change();
            $table->unsignedBigInteger('ar_mars')->change();
            $table->unsignedBigInteger('direct_selling')->change();
            $table->unsignedBigInteger('rumah_club')->change();
            $table->unsignedBigInteger('sewa_dispenser')->change();
            $table->unsignedBigInteger('avalan')->change();
            $table->unsignedBigInteger('fada')->change();
            $table->unsignedBigInteger('jaminan')->change();
            $table->unsignedBigInteger('packaging')->change();
            $table->unsignedBigInteger('galon_afkir')->change();
            $table->unsignedBigInteger('sewa_depo')->change();
            $table->unsignedBigInteger('raw_material')->change();
            $table->unsignedBigInteger('pem_listrik')->change();
            $table->unsignedBigInteger('klaim_sopir')->change();
            $table->unsignedBigInteger('admin_bank')->change();
            $table->unsignedBigInteger('others')->change();
            $table->unsignedBigInteger('subtotal1')->change();
            $table->unsignedBigInteger('subtotal2')->change();
            $table->unsignedBigInteger('subtotal3')->change();
            $table->unsignedBigInteger('grandtotal')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasis', function (Blueprint $table) {
            $table->Integer('iap')->change();
            $table->Integer('adm')->change();
            $table->Integer('potongan')->change();
            $table->Integer('ar_mars')->change();
            $table->Integer('direct_selling')->change();
            $table->Integer('rumah_club')->change();
            $table->Integer('sewa_dispenser')->change();
            $table->Integer('avalan')->change();
            $table->Integer('fada')->change();
            $table->Integer('jaminan')->change();
            $table->Integer('packaging')->change();
            $table->Integer('galon_afkir')->change();
            $table->Integer('sewa_depo')->change();
            $table->Integer('raw_material')->change();
            $table->Integer('pem_listrik')->change();
            $table->Integer('klaim_sopir')->change();
            $table->Integer('admin_bank')->change();
            $table->Integer('others')->change();
            $table->Integer('subtotal1')->change();
            $table->Integer('subtotal2')->change();
            $table->Integer('subtotal3')->change();
            $table->Integer('grandtotal')->change();
        });
    }
};
