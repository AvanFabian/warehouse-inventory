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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant_code', 50)->unique();
            $table->string('variant_name');
            $table->json('attributes')->nullable(); // e.g., {"size": "L", "color": "Red"}
            $table->string('sku_suffix', 50)->nullable(); // e.g., "16-512" for 16GB-512GB
            $table->decimal('purchase_price', 15, 2)->nullable(); // Override parent if set
            $table->decimal('selling_price', 15, 2)->nullable(); // Override parent if set
            $table->string('image')->nullable(); // Override parent if set
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
