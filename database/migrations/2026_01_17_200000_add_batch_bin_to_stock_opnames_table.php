<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add batch_id and bin_id to stock_opnames for batch architecture sync.
     */
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('bin_id')->nullable()->after('batch_id')->constrained('warehouse_bins')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['bin_id']);
            $table->dropColumn(['batch_id', 'bin_id']);
        });
    }
};
