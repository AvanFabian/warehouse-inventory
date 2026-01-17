<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class WarehouseRack extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'code',
        'name',
        'levels',
        'is_active',
    ];

    protected $casts = [
        'levels' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Rack belongs to a zone
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'zone_id');
    }

    /**
     * Rack has many bins
     */
    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class, 'rack_id');
    }

    /**
     * Get the warehouse through zone
     */
    public function getWarehouseAttribute(): ?Warehouse
    {
        return $this->zone?->warehouse;
    }

    /**
     * Scope to get only active racks
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get total bin count in this rack
     */
    public function getBinCountAttribute(): int
    {
        return $this->bins()->count();
    }

    /**
     * Get active bin count
     */
    public function getActiveBinCountAttribute(): int
    {
        return $this->bins()->where('is_active', true)->count();
    }
}
