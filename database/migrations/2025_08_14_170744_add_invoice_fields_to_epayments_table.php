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
        Schema::table('epayments', function (Blueprint $table) {
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->decimal('invoice_amount', 20, 2)->nullable();
            $table->string('tax_invoice')->nullable();
            $table->date('tax_invoice_date')->nullable();
            $table->decimal('tax_amount', 20, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epayments', function (Blueprint $table) {
            //
        });
    }
};
