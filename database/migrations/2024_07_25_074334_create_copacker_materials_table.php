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
        Schema::create('copacker_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('copacker_id')->constrained('copackers')->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials');
            $table->integer('qty');
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copacker_materials');
    }
};
