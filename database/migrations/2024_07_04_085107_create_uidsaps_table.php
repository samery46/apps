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
        Schema::create('uidsaps', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->date('valid_from')->nullable();
            $table->date('valid_end')->nullable();
            $table->string('cost_center');
            $table->string('keterangan');
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
        Schema::dropIfExists('uidsaps');
    }
};
