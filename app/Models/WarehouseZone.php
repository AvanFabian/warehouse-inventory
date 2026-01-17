<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

class WarehouseZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'code',
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Zone belongs to a warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Zone has many racks
     */
    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class, 'zone_id');
    }

    /**
     * Zone has many bins through racks
     */
    public function bins(): HasManyThrough
    {
        return $this->hasManyThrough(
            WarehouseBin::class,
            WarehouseRack::class,
            'zone_id',  // Foreign key on WarehouseRack
            'rack_id',  // Foreign key on WarehouseBin
            'id',       // Local key on WarehouseZone
            'id'        // Local key on WarehouseRack
        );
    }

    /**
     * Scope to get only active zones
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Get total bin count in this zone
     */
    public function getBinCountAttribute(): int
    {
        return $this->bins()->count();
    }

    /**
     * Get total rack count in this zone
     */
    public function getRackCountAttribute(): int
    {
        return $this->racks()->count();
    }
}
