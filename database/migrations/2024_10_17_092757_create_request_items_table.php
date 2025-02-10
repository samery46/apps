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
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('process', 50);
            $table->string('period', 50);
            $table->foreignId('kategori_id')->constrained('kategoris')->cascadeOnDelete();
            $table->text('description');
            $table->string('item_type', 50);
            $table->integer('qty');
            $table->decimal('price', 15, 2);
            $table->string('type_asset', 50);
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->text('job_description');
            $table->string('number_user', 50);
            $table->text('justification');
            $table->text('notes');
            $table->text('estimation');
            $table->text('purchase_history');
            $table->boolean('is_photocopier');
            $table->text('reason');
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
        Schema::dropIfExists('request_items');
    }
};
