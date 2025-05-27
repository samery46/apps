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
        Schema::create('approval_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('level'); // 1, 2, 3
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('position')->nullable(); // optional, misalnya 'Supervisor'
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
        Schema::dropIfExists('approval_settings');
    }
};
