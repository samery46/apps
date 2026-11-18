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
        // 1. Setting Workflow Approval per Plant
        Schema::create('workflow_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('step_order');
            $table->timestamps();

            $table->unique(['plant_id', 'step_order']);
        });

        // 2. Transaksi Utama Klaim
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('incident_type', ['rusak', 'hilang']);
            $table->dateTime('tgl_kejadian');
            $table->decimal('estimasi_kerugian', 15, 2)->default(0);
            $table->text('latar_belakang');
            $table->text('kronologi');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Lampiran Berkas Klaim (Multiple Attachments)
        Schema::create('claim_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->string('nama_file_asli');
            $table->string('file_path');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        // 4. Tracking Sequence Approval
        Schema::create('claim_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('step_order');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('action_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_approvals');
        Schema::dropIfExists('claim_attachments');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('workflow_configs');
    }
};
