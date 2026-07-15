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
        Schema::create('numberings', function (Blueprint $table) {
            $table->id();
            $table->date('tgl');
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete()->nullable();
            $table->foreignId('departemen_id')->constrained('departemens')->cascadeOnDelete()->nullable();
            $table->string('transaction_number')->unique();
            $table->string('hal')->nullable();
            $table->string('kepada')->nullable();
            $table->string('up')->nullable();
            $table->string('alamat')->nullable();
            $table->text('isi')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->nullable();
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
        Schema::dropIfExists('numberings');
    }
};
