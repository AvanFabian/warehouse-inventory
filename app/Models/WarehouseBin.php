<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class WarehouseBin extends Model
{
    use HasFactory;

    protected $fillable = [
        'rack_id',
        'code',
        'barcode',
        'level',
        'max_capacity',
        'pick_priority',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'max_capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Bin belongs to a rack
     */
    public function rack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'rack_id');
    }

    /**
     * Get the zone through rack
     */
    public function getZoneAttribute(): ?WarehouseZone
    {
        return $this->rack?->zone;
    }

    /**
     * Get the warehouse through rack->zone
     */
    public function getWarehouseAttribute(): ?Warehouse
    {
        return $this->rack?->zone?->warehouse;
    }

    /**
     * Get full location path: "WH-CODE/ZONE-CODE/RACK-CODE/BIN-CODE"
     */
    public function getFullPathAttribute(): string
    {
        $warehouse = $this->warehouse;
        $zone = $this->zone;
        $rack = $this->rack;

        return implode('/', [
            $warehouse?->code ?? 'N/A',
            $zone?->code ?? 'N/A',
            $rack?->code ?? 'N/A',
            $this->code,
        ]);
    }

    /**
     * Get current occupancy from stock_locations (will be implemented in Phase B)
     * For now, returns 0 as stock_locations table doesn't exist yet
     */
    public function getCurrentOccupancyAttribute(): int
    {
        // This will be updated in Phase B when stock_locations table is created
        // return $this->stockLocations()->sum('quantity');
        return 0;
    }

    /**
     * Check if bin has capacity for additional quantity
     * 
     * @param int $quantity The quantity to check
     * @return bool
     */
    public function hasCapacity(int $quantity): bool
    {
        // Null capacity means unlimited
        if ($this->max_capacity === null) {
            return true;
        }

        $currentOccupancy = $this->current_occupancy;
        return ($currentOccupancy + $quantity) <= $this->max_capacity;
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacityAttribute(): ?int
    {
        if ($this->max_capacity === null) {
            return null; // Unlimited
        }

        return $this->max_capacity - $this->current_occupancy;
    }

    /**
     * Scope to get only active bins
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by pick priority
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('pick_priority', $priority);
    }

    /**
     * Scope to order by pick priority (high first)
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(pick_priority, 'high', 'medium', 'low')");
    }

    /**
     * Scope to get bins with available capacity
     */
    public function scopeWithCapacity(Builder $query, int $requiredCapacity = 1): Builder
    {
        return $query->where(function ($q) use ($requiredCapacity) {
            $q->whereNull('max_capacity') // Unlimited
              ->orWhereRaw('max_capacity >= ?', [$requiredCapacity]);
        });
    }
}
