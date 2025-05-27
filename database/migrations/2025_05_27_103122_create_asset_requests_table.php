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
        Schema::create('asset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained()->onDelete('cascade');
            $table->string('document_number')->unique();
            $table->foreignId('asset_group_id')->constrained()->onDelete('cascade');
            $table->string('fixed_asset_number')->nullable();
            $table->string('cea_number')->nullable();
            $table->foreignId('cost_center_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable(); // jenis asset
            $table->string('sub_asset_number')->nullable();
            $table->integer('usage_period')->nullable();
            $table->integer('quantity');
            $table->string('condition'); // new or used
            $table->string('item_name');
            $table->string('country_of_origin')->nullable();
            $table->year('year_of_manufacture')->nullable();
            $table->string('supplier')->nullable();
            $table->date('expected_arrival')->nullable();
            $table->date('expected_usage')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_aktif')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_requests');
    }
};
