<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'unit',
        'min_stock',
        'purchase_price',
        'selling_price',
        'image',
        'status'
    ];

    /**
     * Many-to-Many relationship with Warehouse through pivot table
     * Access: $product->warehouses
     * Pivot data: $product->warehouses->first()->pivot->stock
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot(['stock', 'rack_location', 'min_stock'])
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockInDetails()
    {
        return $this->hasMany(StockInDetail::class);
    }

    public function stockOutDetails()
    {
        return $this->hasMany(StockOutDetail::class);
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAttribute()
    {
        return $this->warehouses()->sum('product_warehouse.stock');
    }

    /**
     * Get stock in a specific warehouse
     */
    public function getStockInWarehouse($warehouseId)
    {
        $warehouse = $this->warehouses()->where('warehouse_id', $warehouseId)->first();
        return $warehouse ? $warehouse->pivot->stock : 0;
    }

    /**
     * Check if product has stock in a specific warehouse
     */
    public function hasStockInWarehouse($warehouseId, $quantity = 1)
    {
        return $this->getStockInWarehouse($warehouseId) >= $quantity;
    }
}
