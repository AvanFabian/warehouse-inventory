<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InterWarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'completed_by',
        'completed_at'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate transfer number
        static::creating(function ($transfer) {
            if (empty($transfer->transfer_number)) {
                $transfer->transfer_number = self::generateTransferNumber();
            }
        });
    }

    /**
     * Generate unique transfer number
     */
    public static function generateTransferNumber()
    {
        $date = date('Ymd');
        $lastTransfer = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransfer) {
            $lastNumber = intval(substr($lastTransfer->transfer_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'TRF-' . $date . '-' . $newNumber;
    }

    /**
     * Relationships
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(InterWarehouseTransferItem::class, 'transfer_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Helper methods
     */
    public function getTotalItems()
    {
        return $this->items()->sum('quantity');
    }

    public function canApprove()
    {
        return $this->status === 'pending';
    }

    public function canComplete()
    {
        return in_array($this->status, ['approved', 'in_transit']);
    }

    public function canReject()
    {
        return $this->status === 'pending';
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now()
        ]);
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected'
        ]);
    }

    public function startTransit()
    {
        $this->update([
            'status' => 'in_transit'
        ]);
    }

    public function complete($userId)
    {
        // Update stock for each item in pivot table
        foreach ($this->items as $item) {
            $product = Product::find($item->product_id);

            if ($product) {
                // Reduce stock from source warehouse in pivot table
                if ($product->warehouses()->where('warehouse_id', $this->from_warehouse_id)->exists()) {
                    $product->warehouses()->updateExistingPivot($this->from_warehouse_id, [
                        'stock' => DB::raw('stock - ' . (int)$item->quantity)
                    ]);
                }

                // Add stock to destination warehouse in pivot table
                if ($product->warehouses()->where('warehouse_id', $this->to_warehouse_id)->exists()) {
                    // Product already exists in destination warehouse - just add stock
                    $product->warehouses()->updateExistingPivot($this->to_warehouse_id, [
                        'stock' => DB::raw('stock + ' . (int)$item->quantity)
                    ]);
                } else {
                    // Product doesn't exist in destination warehouse - attach it
                    $product->warehouses()->attach($this->to_warehouse_id, [
                        'stock' => $item->quantity,
                        'rack_location' => null,
                        'min_stock' => null
                    ]);
                }
            }
        }

        $this->update([
            'status' => 'completed',
            'completed_by' => $userId,
            'completed_at' => now()
        ]);
    }
}
