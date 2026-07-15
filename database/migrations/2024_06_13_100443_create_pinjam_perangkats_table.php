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
        Schema::create('pinjam_perangkats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pinjam_id')->constrained('pinjams')->cascadeOnDelete();
            $table->foreignId('perangkat_id')->constrained('perangkats')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjam_perangkats');
    }
};
