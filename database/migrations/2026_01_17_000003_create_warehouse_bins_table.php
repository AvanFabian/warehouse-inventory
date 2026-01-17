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
        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained('warehouse_racks')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('barcode', 50)->unique()->nullable();
            $table->integer('level')->default(1); // Which level on the rack (1 = ground)
            $table->integer('max_capacity')->nullable(); // Max units, null = unlimited
            $table->enum('pick_priority', ['high', 'medium', 'low'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Bin code must be unique within each rack
            $table->unique(['rack_id', 'code']);
            
            // Index for efficient queries
            $table->index(['rack_id', 'is_active', 'pick_priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
    }
};
