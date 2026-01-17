<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\LowStockNotification;
use App\Services\NotificationThrottleService;
use Illuminate\Support\Facades\Notification;

/**
 * StockLocationObserver
 * 
 * Observes stock changes and triggers low stock notifications
 * when a product's stock falls below the reorder point for a warehouse.
 * 
 * Note: Notifications use ShouldQueue, so they will be dispatched
 * after the current request completes via the queue.
 */
class StockLocationObserver
{
    protected NotificationThrottleService $throttleService;

    public function __construct(NotificationThrottleService $throttleService)
    {
        $this->throttleService = $throttleService;
    }

    /**
     * Handle the StockLocation "updated" event.
     * 
     * This fires when stock quantity changes (e.g., after allocation).
     */
    public function updated(StockLocation $stockLocation): void
    {
        // Only check if quantity decreased
        if (!$stockLocation->wasChanged('quantity')) {
            return;
        }

        $oldQuantity = $stockLocation->getOriginal('quantity');
        $newQuantity = $stockLocation->quantity;

        // Only check if quantity decreased
        if ($newQuantity >= $oldQuantity) {
            return;
        }

        $this->checkLowStock($stockLocation);
    }

    /**
     * Handle the StockLocation "deleted" event.
     * 
     * When a stock location is deleted (depleted), check total stock.
     */
    public function deleted(StockLocation $stockLocation): void
    {
        $this->checkLowStock($stockLocation);
    }

    /**
     * Check if product stock is below reorder point in warehouse.
     */
    protected function checkLowStock(StockLocation $stockLocation): void
    {
        $batch = $stockLocation->batch;
        if (!$batch) {
            return;
        }

        $product = $batch->product;
        if (!$product) {
            return;
        }

        // Get the warehouse from the bin hierarchy
        $bin = $stockLocation->bin;
        if (!$bin || !$bin->rack || !$bin->rack->zone) {
            return;
        }

        $warehouse = $bin->rack->zone->warehouse;
        if (!$warehouse) {
            return;
        }

        // Calculate total stock for this product in this warehouse
        $totalStock = $this->calculateTotalStock($product, $warehouse);

        // Get per-warehouse reorder point
        $reorderPoint = $this->getReorderPoint($product, $warehouse);

        // Check if below threshold
        if ($totalStock >= $reorderPoint) {
            return;
        }

        // Check throttling
        $throttleKey = NotificationThrottleService::lowStockKey($product->id, $warehouse->id);
        if (!$this->throttleService->shouldSendNotification($throttleKey)) {
            return;
        }

        // Mark as notified
        $this->throttleService->markNotificationSent($throttleKey);

        // Send notification to all admin users
        // Wrapped in try-catch to prevent notification failures from breaking stock operations
        try {
            $admins = User::where('role', 'admin')->get();
            
            if ($admins->isNotEmpty()) {
                Notification::send(
                    $admins,
                    new LowStockNotification($product, $warehouse, $totalStock, $reorderPoint)
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send low stock notification', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate total available stock for a product in a warehouse.
     * 
     * Sums all stock locations for batches of this product
     * within bins that belong to this warehouse.
     */
    protected function calculateTotalStock(Product $product, Warehouse $warehouse): int
    {
        return StockLocation::whereHas('batch', function ($query) use ($product) {
                $query->where('product_id', $product->id)
                    ->where('status', '!=', 'depleted');
            })
            ->whereHas('bin.rack.zone', function ($query) use ($warehouse) {
                $query->where('warehouse_id', $warehouse->id);
            })
            ->sum('quantity');
    }

    /**
     * Get the reorder point (min_stock) for a product in a warehouse.
     * 
     * Checks the per-warehouse setting first, falls back to product's global min_stock.
     */
    protected function getReorderPoint(Product $product, Warehouse $warehouse): int
    {
        // Check per-warehouse min_stock from pivot table
        $warehouseData = $product->warehouses()
            ->where('warehouse_id', $warehouse->id)
            ->first();

        if ($warehouseData && $warehouseData->pivot->min_stock !== null) {
            return (int) $warehouseData->pivot->min_stock;
        }

        // Fall back to product's global min_stock
        return (int) ($product->min_stock ?? 0);
    }
}
