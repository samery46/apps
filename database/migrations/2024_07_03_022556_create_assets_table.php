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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('sub')->nullable();
            $table->string('tipe')->nullable();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('nama')->nullable();
            $table->date('tgl_perolehan')->nullable();
            $table->integer('harga')->default(0);
            $table->integer('nbv')->default(0);
            $table->string('serial_number')->nullable();
            $table->string('status')->nullable();
            $table->integer('qty_sap')->default(0);
            $table->integer('qty_aktual')->default(0);
            $table->string('kondisi')->nullable();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->string('lokasi')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
