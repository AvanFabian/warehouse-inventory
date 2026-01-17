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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number', 50);
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost_price', 15, 2);
            $table->enum('status', ['active', 'expired', 'depleted', 'quarantine'])->default('active');
            $table->foreignId('stock_in_id')->nullable()->constrained('stock_ins')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Batch number must be unique within each product
            $table->unique(['product_id', 'batch_number']);
            
            // Indexes for efficient queries
            $table->index(['product_id', 'status']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
