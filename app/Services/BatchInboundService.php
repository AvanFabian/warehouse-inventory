<?php

namespace App\Services;

use App\Exceptions\InsufficientCapacityException;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service class for handling batch inbound (putaway/stock-in) operations.
 * 
 * Implements "Consolidation & Spillover" strategy:
 * 1. Consolidation: Fill existing bins with same batch first
 * 2. Empty Bin: Use first empty bin if no consolidation possible
 * 3. Spillover: Split across bins if quantity exceeds single bin capacity
 */
class BatchInboundService
{
    /**
     * Suggest putaway locations for a batch with given quantity.
     * Does NOT modify any data - only returns suggested locations.
     * 
     * @param Batch $batch The batch to place
     * @param Warehouse $warehouse Target warehouse
     * @param int $quantity Quantity to place
     * @return Collection<int, array{bin: WarehouseBin, quantity: int, is_consolidation: bool}>
     * @throws InsufficientCapacityException
     */
    public function suggestPutawayLocation(Batch $batch, Warehouse $warehouse, int $quantity): Collection
    {
        $suggestions = collect();
        $remainingQuantity = $quantity;

        // PRIORITY 1: Consolidation - Find existing bins with same batch
        $existingLocations = StockLocation::where('batch_id', $batch->id)
            ->whereHas('bin', function ($query) use ($warehouse) {
                $query->active()
                    ->whereHas('rack.zone', function ($q) use ($warehouse) {
                        $q->where('warehouse_id', $warehouse->id)
                          ->where('type', 'storage')
                          ->where('is_active', true);
                    });
            })
            ->with('bin')
            ->get();

        foreach ($existingLocations as $location) {
            if ($remainingQuantity <= 0) break;

            $bin = $location->bin;
            $availableCapacity = $bin->available_capacity;

            if ($availableCapacity === null || $availableCapacity > 0) {
                $fillQuantity = $availableCapacity === null 
                    ? $remainingQuantity 
                    : min($remainingQuantity, $availableCapacity);

                $suggestions->push([
                    'bin' => $bin,
                    'bin_id' => $bin->id,
                    'quantity' => $fillQuantity,
                    'is_consolidation' => true,
                ]);

                $remainingQuantity -= $fillQuantity;
            }
        }

        // PRIORITY 2: Empty bins in storage zones
        if ($remainingQuantity > 0) {
            $emptyBins = $this->findAvailableBins($warehouse, $remainingQuantity);

            foreach ($emptyBins as $bin) {
                if ($remainingQuantity <= 0) break;

                $availableCapacity = $bin->available_capacity;
                $fillQuantity = $availableCapacity === null
                    ? $remainingQuantity
                    : min($remainingQuantity, $availableCapacity);

                $suggestions->push([
                    'bin' => $bin,
                    'bin_id' => $bin->id,
                    'quantity' => $fillQuantity,
                    'is_consolidation' => false,
                ]);

                $remainingQuantity -= $fillQuantity;
            }
        }

        // Check if we could place everything
        if ($remainingQuantity > 0) {
            $totalCapacity = $this->getTotalAvailableCapacity($warehouse);
            throw new InsufficientCapacityException(
                $quantity,
                $quantity - $remainingQuantity,
                $warehouse->id
            );
        }

        return $suggestions;
    }

    /**
     * Execute putaway operation - places batch into bins.
     * 
     * @param Batch $batch The batch to place
     * @param Warehouse $warehouse Target warehouse
     * @param int $quantity Quantity to place
     * @param User|null $user User performing the operation
     * @param mixed $reference Optional reference (e.g., StockIn record)
     * @return Collection<int, array{bin_id: int, quantity: int}>
     * @throws InsufficientCapacityException
     */
    public function putaway(
        Batch $batch,
        Warehouse $warehouse,
        int $quantity,
        ?User $user = null,
        $reference = null
    ): Collection {
        return DB::transaction(function () use ($batch, $warehouse, $quantity, $user, $reference) {
            $placements = collect();
            $remainingQuantity = $quantity;

            // PRIORITY 1: Consolidation - Find existing locations with same batch
            $existingLocations = StockLocation::where('batch_id', $batch->id)
                ->whereHas('bin', function ($query) use ($warehouse) {
                    $query->active()
                        ->whereHas('rack.zone', function ($q) use ($warehouse) {
                            $q->where('warehouse_id', $warehouse->id)
                              ->where('type', 'storage')
                              ->where('is_active', true);
                        });
                })
                ->lockForUpdate() // CRITICAL: Lock for race condition prevention
                ->with('bin')
                ->get();

            foreach ($existingLocations as $location) {
                if ($remainingQuantity <= 0) break;

                // Refresh to get latest capacity after lock
                $location->bin->refresh();
                $bin = $location->bin;
                $availableCapacity = $bin->available_capacity;

                if ($availableCapacity === null || $availableCapacity > 0) {
                    $fillQuantity = $availableCapacity === null
                        ? $remainingQuantity
                        : min($remainingQuantity, $availableCapacity);

                    $quantityBefore = $location->quantity;
                    $location->quantity += $fillQuantity;
                    $location->save();

                    $this->recordMovement(
                        $batch,
                        $bin,
                        'stock_in',
                        $fillQuantity,
                        $quantityBefore,
                        $location->quantity,
                        $user,
                        $reference
                    );

                    $placements->push([
                        'bin_id' => $bin->id,
                        'quantity' => $fillQuantity,
                        'is_consolidation' => true,
                    ]);

                    $remainingQuantity -= $fillQuantity;
                }
            }

            // PRIORITY 2: Find new bins with capacity
            if ($remainingQuantity > 0) {
                $availableBins = $this->findAvailableBinsWithLock($warehouse, $remainingQuantity);

                foreach ($availableBins as $bin) {
                    if ($remainingQuantity <= 0) break;

                    $availableCapacity = $bin->available_capacity;
                    $fillQuantity = $availableCapacity === null
                        ? $remainingQuantity
                        : min($remainingQuantity, $availableCapacity);

                    // Check if location already exists (shouldn't for new bins, but be safe)
                    $stockLocation = StockLocation::firstOrCreate(
                        ['batch_id' => $batch->id, 'bin_id' => $bin->id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );

                    $quantityBefore = $stockLocation->quantity;
                    $stockLocation->quantity += $fillQuantity;
                    $stockLocation->save();

                    $this->recordMovement(
                        $batch,
                        $bin,
                        'stock_in',
                        $fillQuantity,
                        $quantityBefore,
                        $stockLocation->quantity,
                        $user,
                        $reference
                    );

                    $placements->push([
                        'bin_id' => $bin->id,
                        'quantity' => $fillQuantity,
                        'is_consolidation' => false,
                    ]);

                    $remainingQuantity -= $fillQuantity;
                }
            }

            // Check if we could place everything
            if ($remainingQuantity > 0) {
                throw new InsufficientCapacityException(
                    $quantity,
                    $quantity - $remainingQuantity,
                    $warehouse->id
                );
            }

            return $placements;
        });
    }

    /**
     * Find available bins with capacity in storage zones.
     */
    protected function findAvailableBins(Warehouse $warehouse, int $requiredCapacity): Collection
    {
        return WarehouseBin::active()
            ->whereHas('rack.zone', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id)
                      ->where('type', 'storage')
                      ->where('is_active', true);
            })
            ->where(function ($query) use ($requiredCapacity) {
                // NULL max_capacity means unlimited
                $query->whereNull('max_capacity')
                      ->orWhereRaw('max_capacity - COALESCE((SELECT SUM(quantity) FROM stock_locations WHERE bin_id = warehouse_bins.id), 0) > 0');
            })
            ->orderByPriority()
            ->orderBy('id') // Consistent ordering for tests
            ->get();
    }

    /**
     * Find available bins with locking for race condition prevention.
     */
    protected function findAvailableBinsWithLock(Warehouse $warehouse, int $requiredCapacity): Collection
    {
        return WarehouseBin::active()
            ->whereHas('rack.zone', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id)
                      ->where('type', 'storage')
                      ->where('is_active', true);
            })
            ->where(function ($query) use ($requiredCapacity) {
                $query->whereNull('max_capacity')
                      ->orWhereRaw('max_capacity - COALESCE((SELECT SUM(quantity) FROM stock_locations WHERE bin_id = warehouse_bins.id), 0) > 0');
            })
            ->lockForUpdate() // CRITICAL: Lock for race condition prevention
            ->orderByPriority()
            ->orderBy('id')
            ->get();
    }

    /**
     * Get total available capacity in warehouse storage zones.
     */
    protected function getTotalAvailableCapacity(Warehouse $warehouse): int
    {
        $bins = WarehouseBin::active()
            ->whereHas('rack.zone', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id)
                      ->where('type', 'storage')
                      ->where('is_active', true);
            })
            ->get();

        $total = 0;
        $hasUnlimited = false;

        foreach ($bins as $bin) {
            if ($bin->max_capacity === null) {
                $hasUnlimited = true;
                break;
            }
            $total += $bin->available_capacity ?? 0;
        }

        return $hasUnlimited ? PHP_INT_MAX : $total;
    }

    /**
     * Record a batch movement for audit trail.
     */
    protected function recordMovement(
        Batch $batch,
        WarehouseBin $bin,
        string $movementType,
        int $quantity,
        int $quantityBefore,
        int $quantityAfter,
        ?User $user = null,
        $reference = null
    ): BatchMovement {
        return BatchMovement::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'user_id' => $user?->id,
        ]);
    }
}
