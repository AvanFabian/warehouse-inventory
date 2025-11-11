<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all existing products with warehouse assignments
        $products = DB::table('products')->whereNotNull('warehouse_id')->get();

        foreach ($products as $product) {
            // Insert into pivot table
            DB::table('product_warehouse')->insert([
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'stock' => $product->stock ?? 0,
                'rack_location' => $product->rack_location,
                'min_stock' => null, // Use global min_stock from products table
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear pivot table (rollback)
        DB::table('product_warehouse')->truncate();
    }
};
