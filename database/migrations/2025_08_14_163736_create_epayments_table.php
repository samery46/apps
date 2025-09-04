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
        Schema::create('epayments', function (Blueprint $table) {
            $table->id();
            $table->string('bill_to');
            $table->string('bill_to_name');
            $table->string('vendor');
            $table->string('vendor_name');
            $table->string('ticket_no');
            $table->date('ticket_date');
            $table->string('miro_no')->nullable();
            $table->date('miro_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->string('po_no');
            $table->string('sj_no');
            $table->string('gr_no');
            $table->decimal('amount', 20, 2);
            $table->string('status');
            $table->date('created_date');
            $table->time('created_time');
            $table->string('confirm_by')->nullable();
            $table->string('composite_key')->unique();
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epayments');
    }
};
