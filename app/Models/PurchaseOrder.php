<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'warehouse_id',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'approved_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['approved', 'partially_received']);
    }

    public function getRemainingQuantity(int $productId): int
    {
        $detail = $this->details()->where('product_id', $productId)->first();
        if (!$detail) {
            return 0;
        }
        return $detail->quantity_ordered - $detail->quantity_received;
    }

    public function isFullyReceived(): bool
    {
        foreach ($this->details as $detail) {
            if ($detail->quantity_received < $detail->quantity_ordered) {
                return false;
            }
        }
        return true;
    }

    public static function generatePoNumber(): string
    {
        $date = now()->format('Ymd');
        $lastPo = self::whereDate('created_at', now())->latest()->first();

        if ($lastPo) {
            $lastNumber = (int) substr($lastPo->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PO-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
