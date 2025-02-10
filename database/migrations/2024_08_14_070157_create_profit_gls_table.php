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
        Schema::create('profit_gls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profit_id')->constrained('profits')->cascadeOnDelete();
            $table->foreignId('gl_id')->constrained('gls')->cascadeOnDelete();
            $table->integer('value');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_gls');
    }
};
