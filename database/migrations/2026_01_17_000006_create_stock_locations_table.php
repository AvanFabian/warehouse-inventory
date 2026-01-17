<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * stock_locations table maps batches to bins with quantities.
     * This supports multi-bin placement (same batch across different bins).
     */
    public function up(): void
    {
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bin_id')->constrained('warehouse_bins')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // For pending orders
            $table->timestamps();
            
            // Each batch can only have one entry per bin
            $table->unique(['batch_id', 'bin_id']);
            
            // Index for efficient bin capacity queries
            $table->index(['bin_id', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_locations');
    }
};
