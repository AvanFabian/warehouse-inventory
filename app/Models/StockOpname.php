<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id', 
        'product_id', 
        'batch_id',
        'bin_id',
        'system_qty', 
        'counted_qty', 
        'difference', 
        'reason', 
        'date', 
        'user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'system_qty' => 'integer',
        'counted_qty' => 'integer',
        'difference' => 'integer',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function bin()
    {
        return $this->belongsTo(WarehouseBin::class, 'bin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
