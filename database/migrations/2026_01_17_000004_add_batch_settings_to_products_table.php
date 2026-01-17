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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('enable_batch_tracking')->default(false)->after('status');
            $table->enum('batch_method', ['FIFO', 'LIFO', 'FEFO'])->default('FIFO')->after('enable_batch_tracking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['enable_batch_tracking', 'batch_method']);
        });
    }
};
