<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_active',
        'is_default',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent deletion if warehouse has transactions
        static::deleting(function ($warehouse) {
            if ($warehouse->products()->count() > 0 ||
                $warehouse->stockIns()->count() > 0 ||
                $warehouse->stockOuts()->count() > 0 ||
                $warehouse->stockOpnames()->count() > 0 ||
                $warehouse->transfersFrom()->count() > 0 ||
                $warehouse->transfersTo()->count() > 0) {
                throw new \Exception('Cannot delete warehouse with existing transactions or products.');
            }
        });
    }

    /**
     * Relationships
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function stockOpnames()
    {
        return $this->hasMany(StockOpname::class);
    }

    public function transfersFrom()
    {
        return $this->hasMany(InterWarehouseTransfer::class, 'from_warehouse_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(InterWarehouseTransfer::class, 'to_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Helper methods
     */
    public function getTotalStockValue()
    {
        return $this->products()->sum(DB::raw('stock * purchase_price'));
    }

    public function getProductCount()
    {
        return $this->products()->count();
    }
}