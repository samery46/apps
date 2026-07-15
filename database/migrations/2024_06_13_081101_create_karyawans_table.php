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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nik')->unique();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('uid_sap')->unique()->nullable();
            $table->string('user_ad')->unique()->nullable();
            $table->string('computer_name')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('status')->nullable();
            $table->string('foto')->nullable();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('departemen_id')->constrained('departemens')->cascadeOnDelete();
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
        Schema::dropIfExists('karyawans');
    }
};
