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
        Schema::table('copacks', function (Blueprint $table) {
            //
            $table->foreignId('type_id')
                ->nullable()
                ->after('qty')
                ->constrained('type')
                ->cascadeOnDelete();
            $table->string('vendor')
                ->nullable()
                ->before('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('copacks', function (Blueprint $table) {
            //
            $table->dropForeign(['type_id']);
            $table->dropColumn('type_id', 'vendor');
        });
    }
};
