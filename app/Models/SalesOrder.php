<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'so_number',
        'customer_id',
        'warehouse_id',
        'order_date',
        'delivery_date',
        'status',
        'payment_status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'stock_out_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Generate SO number
    public static function generateSONumber()
    {
        $lastSO = self::whereDate('created_at', today())->latest()->first();
        $number = $lastSO ? (int)substr($lastSO->so_number, -5) + 1 : 1;

        return 'SO-' . date('Ymd') . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
