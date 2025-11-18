<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_code',
        'variant_name',
        'attributes',
        'sku_suffix',
        'purchase_price',
        'selling_price',
        'image',
        'status'
    ];

    protected $casts = [
        'attributes' => 'array',
        'status' => 'boolean',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    /**
     * Belongs to parent product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Many-to-Many relationship with Warehouse through pivot table
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot(['stock', 'rack_location', 'min_stock'])
            ->withTimestamps();
    }

    /**
     * Get total stock across all warehouses for this variant
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
     * Get effective purchase price (use variant price or fallback to product price)
     */
    public function getEffectivePurchasePriceAttribute()
    {
        return $this->purchase_price ?? $this->product->purchase_price;
    }

    /**
     * Get effective selling price (use variant price or fallback to product price)
     */
    public function getEffectiveSellingPriceAttribute()
    {
        return $this->selling_price ?? $this->product->selling_price;
    }

    /**
     * Get full display name (Product Name - Variant Name)
     */
    public function getFullNameAttribute()
    {
        return $this->product->name . ' - ' . $this->variant_name;
    }

    /**
     * Get formatted attributes for display
     */
    public function getFormattedAttributesAttribute()
    {
        if (empty($this->attributes)) {
            return '';
        }

        return collect($this->attributes)
            ->map(fn($value, $key) => ucfirst($key) . ': ' . $value)
            ->join(', ');
    }
}
