<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service class for handling batch allocation (stock-out) operations.
 * 
 * Implements FIFO/LIFO/FEFO allocation based on product settings.
 */
class BatchAllocationService
{
    /**
     * Allocate stock from batches for a product.
     * 
     * @param Product $product The product to allocate
     * @param Warehouse $warehouse Source warehouse
     * @param int $quantity Quantity to allocate
     * @param string|null $batchNumber Optional specific batch to allocate from
     * @param User|null $user User performing the operation
     * @param mixed $reference Optional reference (e.g., StockOut record)
     * @return Collection<int, array{batch: Batch, quantity: int, bin_id: int}>
     * @throws InsufficientStockException
     */
    public function allocate(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $batchNumber = null,
        ?User $user = null,
        $reference = null
    ): Collection {
        return DB::transaction(function () use ($product, $warehouse, $quantity, $batchNumber, $user, $reference) {
            $allocations = collect();
            $remainingQuantity = $quantity;

            // Get allocation method from product (FIFO, LIFO, FEFO)
            $method = $product->batch_method ?? 'FIFO';

            // Find stock locations with available stock in this warehouse for this product
            // Using stock locations as the source of truth, then get their batches
            $stockLocationsQuery = StockLocation::whereHas('batch', function ($batchQuery) use ($product) {
                    $batchQuery->where('product_id', $product->id)
                        ->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>', now());
                        });
                })
                ->whereHas('bin', function ($binQuery) use ($warehouse) {
                    $binQuery->whereHas('rack', function ($rackQuery) use ($warehouse) {
                        $rackQuery->whereHas('zone', function ($zoneQuery) use ($warehouse) {
                            $zoneQuery->where('warehouse_id', $warehouse->id);
                        });
                    });
                })
                ->whereRaw('quantity > COALESCE(reserved_quantity, 0)');

            // Apply ordering using subquery to avoid join + eager load conflict
            if ($method === 'FIFO') {
                $stockLocationsQuery->orderBy(
                    Batch::select('created_at')
                        ->whereColumn('batches.id', 'stock_locations.batch_id')
                        ->limit(1),
                    'asc'
                );
            } elseif ($method === 'LIFO') {
                $stockLocationsQuery->orderBy(
                    Batch::select('created_at')
                        ->whereColumn('batches.id', 'stock_locations.batch_id')
                        ->limit(1),
                    'desc'
                );
            } elseif ($method === 'FEFO') {
                // Order by expiry_date ASC (NULLs last) using subquery pattern
                $stockLocationsQuery->orderByRaw(
                    '(SELECT COALESCE(expiry_date, "9999-12-31") FROM batches WHERE batches.id = stock_locations.batch_id) ASC'
                );
            }

            // If specific batch requested, filter to that
            if ($batchNumber) {
                $stockLocationsQuery->whereHas('batch', function ($q) use ($batchNumber) {
                    $q->where('batch_number', $batchNumber);
                });
            }

            // Eager load after query construction
            $stockLocations = $stockLocationsQuery->with(['batch', 'bin'])->lockForUpdate()->get();

            foreach ($stockLocations as $location) {
                if ($remainingQuantity <= 0) break;

                $batch = $location->batch;
                $available = $location->quantity - ($location->reserved_quantity ?? 0);
                $allocateQuantity = min($remainingQuantity, $available);

                if ($allocateQuantity > 0) {
                    $quantityBefore = $location->quantity;
                    $location->quantity -= $allocateQuantity;
                    $location->save();

                    $this->recordMovement(
                        $batch,
                        $location->bin,
                        'stock_out',
                        -$allocateQuantity, // Negative for stock out
                        $quantityBefore,
                        $location->quantity,
                        $user,
                        $reference
                    );

                    $allocations->push([
                        'batch' => $batch,
                        'batch_id' => $batch->id,
                        'quantity' => $allocateQuantity,
                        'bin' => $location->bin,
                    ]);

                    $remainingQuantity -= $allocateQuantity;

                    // Clean up empty stock locations
                    if ($location->quantity <= 0) {
                        $location->delete();
                    }

                    // Update batch status if depleted
                    $batch->refresh();
                    if ($batch->total_quantity <= 0) {
                        $batch->update(['status' => 'depleted']);
                    }
                }
            }

            // Check if we could allocate everything
            if ($remainingQuantity > 0) {
                $availableQuantity = $this->getAvailableQuantity($product, $warehouse);
                throw new InsufficientStockException(
                    $quantity,
                    $availableQuantity,
                    $product->id
                );
            }

            return $allocations;
        });
    }

    /**
     * Reserve stock without actually allocating.
     * Used for pending orders to prevent overselling.
     * 
     * @param Product $product The product to reserve
     * @param Warehouse $warehouse Source warehouse
     * @param int $quantity Quantity to reserve
     * @return Collection<int, array{batch: Batch, quantity: int}>
     * @throws InsufficientStockException
     */
    public function reserve(Product $product, Warehouse $warehouse, int $quantity): Collection
    {
        return DB::transaction(function () use ($product, $warehouse, $quantity) {
            $reservations = collect();
            $remainingQuantity = $quantity;

            $method = $product->batch_method ?? 'FIFO';

            $batches = Batch::where('product_id', $product->id)
                ->active()
                ->notExpired()
                ->inWarehouse($warehouse->id)
                ->withAvailableStock()
                ->byMethod($method)
                ->get();

            foreach ($batches as $batch) {
                if ($remainingQuantity <= 0) break;

                $stockLocations = StockLocation::where('batch_id', $batch->id)
                    ->whereHas('bin', function ($binQuery) use ($warehouse) {
                        $binQuery->whereHas('rack', function ($rackQuery) use ($warehouse) {
                            $rackQuery->whereHas('zone', function ($zoneQuery) use ($warehouse) {
                                $zoneQuery->where('warehouse_id', $warehouse->id);
                            });
                        });
                    })
                    ->whereRaw('quantity > COALESCE(reserved_quantity, 0)')
                    ->lockForUpdate()
                    ->get();

                foreach ($stockLocations as $location) {
                    if ($remainingQuantity <= 0) break;

                    $available = $location->available;
                    $reserveQuantity = min($remainingQuantity, $available);

                    if ($reserveQuantity > 0) {
                        $location->reserved_quantity += $reserveQuantity;
                        $location->save();

                        $reservations->push([
                            'batch' => $batch,
                            'batch_id' => $batch->id,
                            'location_id' => $location->id,
                            'quantity' => $reserveQuantity,
                        ]);

                        $remainingQuantity -= $reserveQuantity;
                    }
                }
            }

            if ($remainingQuantity > 0) {
                $availableQuantity = $this->getAvailableQuantity($product, $warehouse);
                throw new InsufficientStockException(
                    $quantity,
                    $availableQuantity,
                    $product->id
                );
            }

            return $reservations;
        });
    }

    /**
     * Release reserved stock.
     * 
     * @param array $reservations Array of reservation records to release
     */
    public function releaseReservations(array $reservations): void
    {
        DB::transaction(function () use ($reservations) {
            foreach ($reservations as $reservation) {
                $location = StockLocation::lockForUpdate()->find($reservation['location_id']);
                if ($location) {
                    $location->reserved_quantity = max(0, $location->reserved_quantity - $reservation['quantity']);
                    $location->save();
                }
            }
        });
    }

    /**
     * Get total available quantity for a product in a warehouse.
     */
    public function getAvailableQuantity(Product $product, Warehouse $warehouse): int
    {
        return Batch::where('product_id', $product->id)
            ->active()
            ->notExpired()
            ->inWarehouse($warehouse->id)
            ->get()
            ->sum('available_quantity');
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
