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
        Schema::create('copackers', function (Blueprint $table) {
            $table->id();
            $table->date('tgl');
            $table->foreignId('plant_id')->constrained('plants');
            $table->foreignId('type_transaksi_id')->constrained('type_transaksis');
            $table->string('no_doc');
            $table->string('supplier')->nullable();
            $table->string('nopol')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copackers');
    }
};
