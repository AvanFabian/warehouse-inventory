<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockOpname;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the default warehouse
        $defaultWarehouse = Warehouse::where('is_default', true)->first();

        if ($defaultWarehouse) {
            // Assign all existing products to default warehouse
            Product::whereNull('warehouse_id')->update(['warehouse_id' => $defaultWarehouse->id]);

            // Assign all existing stock ins to default warehouse
            StockIn::whereNull('warehouse_id')->update(['warehouse_id' => $defaultWarehouse->id]);

            // Assign all existing stock outs to default warehouse
            StockOut::whereNull('warehouse_id')->update(['warehouse_id' => $defaultWarehouse->id]);

            // Assign all existing stock opnames to default warehouse
            StockOpname::whereNull('warehouse_id')->update(['warehouse_id' => $defaultWarehouse->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: revert the changes if needed
        Product::update(['warehouse_id' => null]);
        StockIn::update(['warehouse_id' => null]);
        StockOut::update(['warehouse_id' => null]);
        StockOpname::update(['warehouse_id' => null]);
    }
};
