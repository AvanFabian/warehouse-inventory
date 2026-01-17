<?php

namespace App\Models;

use App\Observers\StockLocationObserver;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([StockLocationObserver::class])]
class StockLocation extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'batch_id',
        'bin_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class);
    }

    // ============================================
    // COMPUTED ATTRIBUTES
    // ============================================

    /**
     * Get available quantity (quantity - reserved)
     */
    public function getAvailableAttribute(): int
    {
        return $this->quantity - ($this->reserved_quantity ?? 0);
    }

    /**
     * Check if this stock location has available stock
     */
    public function hasAvailable(int $quantity = 1): bool
    {
        return $this->available >= $quantity;
    }
}
