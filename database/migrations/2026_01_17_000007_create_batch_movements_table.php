<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * batch_movements table provides audit trail for all batch quantity changes.
     */
    public function up(): void
    {
        Schema::create('batch_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
            $table->enum('movement_type', ['stock_in', 'stock_out', 'transfer', 'adjustment', 'return']);
            $table->integer('quantity'); // Positive for in, negative for out
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type', 100)->nullable(); // e.g., 'App\Models\StockIn'
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for audit queries
            $table->index(['batch_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['movement_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_movements');
    }
};
