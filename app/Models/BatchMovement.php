<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'bin_id',
        'movement_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return $this->reference_type::find($this->reference_id);
    }

    // ============================================
    // HELPERS
    // ============================================

    /**
     * Check if this is an inbound movement
     */
    public function isInbound(): bool
    {
        return in_array($this->movement_type, ['stock_in', 'return']);
    }

    /**
     * Check if this is an outbound movement
     */
    public function isOutbound(): bool
    {
        return in_array($this->movement_type, ['stock_out']);
    }
}
