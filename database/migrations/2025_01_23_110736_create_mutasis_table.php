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
        Schema::create('mutasis', function (Blueprint $table) {
            $table->id();
            $table->date('tgl')->nullable();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('periode');
            $table->integer('iap')->nullable();
            $table->integer('adm')->nullable();
            $table->integer('potongan')->nullable();
            $table->integer('ar_mars')->nullable();
            $table->integer('direct_selling')->nullable();
            $table->integer('rumah_club')->nullable();
            $table->integer('sewa_dispenser')->nullable();
            $table->integer('avalan')->nullable();
            $table->integer('fada')->nullable();
            $table->integer('jaminan')->nullable();
            $table->integer('packaging')->nullable();
            $table->integer('galon_afkir')->nullable();
            $table->integer('sewa_depo')->nullable();
            $table->integer('raw_material')->nullable();
            $table->integer('pem_listrik')->nullable();
            $table->integer('klaim_sopir')->nullable();
            $table->integer('admin_bank')->nullable();
            $table->integer('others')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasis');
    }
};
