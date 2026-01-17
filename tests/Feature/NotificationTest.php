<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseRack;
use App\Models\WarehouseZone;
use App\Notifications\BatchExpiryNotification;
use App\Notifications\LowStockNotification;
use App\Services\BatchAllocationService;
use App\Services\NotificationThrottleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * TDD Tests for Proactive Monitoring System (Phase E)
 * 
 * Tests notification system for:
 * - Low Stock Alerts (per warehouse)
 * - Batch Expiry Alerts
 * - Notification throttling to prevent spam
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $adminUser;
    protected Warehouse $warehouse;
    protected WarehouseZone $zone;
    protected WarehouseRack $rack;
    protected WarehouseBin $bin;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'staff']);
        $this->adminUser = User::factory()->create(['role' => 'admin']);

        $this->warehouse = Warehouse::create([
            'name' => 'Notification Test Warehouse',
            'code' => 'WH-NOTIFY',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-NOTIFY',
            'name' => 'Notification Zone',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->rack = WarehouseRack::create([
            'zone_id' => $this->zone->id,
            'code' => 'RACK-NOTIFY',
            'name' => 'Notification Rack',
            'is_active' => true,
        ]);

        $this->bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-NOTIFY',
            'max_capacity' => 500,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Notification Test Category',
        ]);

        $this->product = Product::create([
            'code' => 'PROD-NOTIFY',
            'name' => 'Notification Test Product',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'min_stock' => 50, // Global min_stock
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FIFO',
        ]);

        // Attach product to warehouse with per-warehouse min_stock (reorder_point)
        $this->product->warehouses()->attach($this->warehouse->id, [
            'stock' => 0,
            'min_stock' => 20, // Per-warehouse reorder point
        ]);

        // Clear cache before each test
        Cache::flush();
    }

    // ============================================
    // LOW STOCK NOTIFICATION TESTS
    // ============================================

    /**
     * Test that low stock triggers a database notification.
     * 
     * When stock falls below the per-warehouse min_stock (reorder_point),
     * a database notification should be sent to admin users.
     */
    public function test_low_stock_triggers_database_notification(): void
    {
        Notification::fake();

        // Create batch with stock at reorder point
        $batch = Batch::create([
            'batch_number' => 'BATCH-LOW-STOCK',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        // Create stock location with quantity at min_stock threshold
        $stockLocation = StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 25, // Above min_stock (20)
            'reserved_quantity' => 0,
        ]);

        // Use the allocation service to reduce stock below threshold
        $allocationService = app(BatchAllocationService::class);
        
        // Allocate 10 units (25 - 10 = 15, which is below min_stock of 20)
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            10,
            null,
            $this->user,
            null
        );

        // Verify admin was notified
        Notification::assertSentTo(
            $this->adminUser,
            LowStockNotification::class,
            function ($notification, $channels) {
                return in_array('database', $channels) 
                    && $notification->product->id === $this->product->id
                    && $notification->warehouse->id === $this->warehouse->id;
            }
        );
    }

    /**
     * Test that low stock is calculated per warehouse.
     * 
     * A product may be low in Warehouse A but have plenty in Warehouse B.
     * Notification should only trigger for the low warehouse.
     */
    public function test_low_stock_is_calculated_per_warehouse(): void
    {
        Notification::fake();

        // Create second warehouse with plenty of stock
        $warehouse2 = Warehouse::create([
            'name' => 'Second Warehouse',
            'code' => 'WH-SECOND',
            'is_active' => true,
        ]);

        $zone2 = WarehouseZone::create([
            'warehouse_id' => $warehouse2->id,
            'code' => 'ZONE-2',
            'name' => 'Zone 2',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $rack2 = WarehouseRack::create([
            'zone_id' => $zone2->id,
            'code' => 'RACK-2',
            'name' => 'Rack 2',
            'is_active' => true,
        ]);

        $bin2 = WarehouseBin::create([
            'rack_id' => $rack2->id,
            'code' => 'BIN-2',
            'max_capacity' => 500,
            'is_active' => true,
        ]);

        // Attach product to second warehouse (plenty of stock)
        $this->product->warehouses()->attach($warehouse2->id, [
            'stock' => 100,
            'min_stock' => 20,
        ]);

        // Create batch in warehouse 2 (plenty of stock)
        $batch2 = Batch::create([
            'batch_number' => 'BATCH-WH2',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch2->id,
            'bin_id' => $bin2->id,
            'quantity' => 100, // Well above min_stock
            'reserved_quantity' => 0,
        ]);

        // Create batch in warehouse 1 with low stock
        $batch1 = Batch::create([
            'batch_number' => 'BATCH-WH1-LOW',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $stockLocation = StockLocation::create([
            'batch_id' => $batch1->id,
            'bin_id' => $this->bin->id,
            'quantity' => 25,
            'reserved_quantity' => 0,
        ]);

        // Allocate to trigger low stock in warehouse 1
        $allocationService = app(BatchAllocationService::class);
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            10, // 25 - 10 = 15, below min_stock
            null,
            $this->user,
            null
        );

        // Verify notification was sent for warehouse 1 only
        Notification::assertSentTo(
            $this->adminUser,
            LowStockNotification::class,
            function ($notification) {
                return $notification->warehouse->id === $this->warehouse->id;
            }
        );
    }

    /**
     * Test out-of-stock sends mail notification (high priority).
     */
    public function test_out_of_stock_sends_mail_notification(): void
    {
        Notification::fake();

        // Create batch with stock
        $batch = Batch::create([
            'batch_number' => 'BATCH-OUT-OF-STOCK',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $stockLocation = StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        // Allocate all stock (completely depletes)
        $allocationService = app(BatchAllocationService::class);
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            10, // Depletes all stock
            null,
            $this->user,
            null
        );

        // Verify mail notification was sent for out-of-stock (high priority)
        Notification::assertSentTo(
            $this->adminUser,
            LowStockNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) 
                    && $notification->isOutOfStock === true;
            }
        );
    }

    // ============================================
    // EXPIRY NOTIFICATION TESTS
    // ============================================

    /**
     * Test that expiring batch triggers an alert.
     */
    public function test_expiring_batch_triggers_alert(): void
    {
        Notification::fake();

        // Create batch expiring in 15 days (within 30-day window)
        $expiringBatch = Batch::create([
            'batch_number' => 'BATCH-EXPIRING-SOON',
            'product_id' => $this->product->id,
            'expiry_date' => Carbon::now()->addDays(15),
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $expiringBatch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        // Create batch NOT expiring soon (90 days out)
        $safeBatch = Batch::create([
            'batch_number' => 'BATCH-SAFE',
            'product_id' => $this->product->id,
            'expiry_date' => Carbon::now()->addDays(90),
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $safeBatch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        // Run the expiry check command
        $this->artisan('inventory:check-expiring-batches')
            ->assertSuccessful();

        // Verify notification sent for expiring batch only
        Notification::assertSentTo(
            $this->adminUser,
            BatchExpiryNotification::class,
            function ($notification) use ($expiringBatch) {
                return $notification->batch->id === $expiringBatch->id;
            }
        );

        // Verify notification NOT sent for safe batch
        Notification::assertNotSentTo(
            $this->adminUser,
            BatchExpiryNotification::class,
            function ($notification) use ($safeBatch) {
                return $notification->batch->id === $safeBatch->id;
            }
        );
    }

    /**
     * Test expiry check is configurable.
     */
    public function test_expiry_check_days_is_configurable(): void
    {
        Notification::fake();

        // Create batch expiring in 45 days
        $batch = Batch::create([
            'batch_number' => 'BATCH-45-DAYS',
            'product_id' => $this->product->id,
            'expiry_date' => Carbon::now()->addDays(45),
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        // Run with default 30 days - should NOT trigger
        $this->artisan('inventory:check-expiring-batches --days=30')
            ->assertSuccessful();

        Notification::assertNotSentTo(
            $this->adminUser,
            BatchExpiryNotification::class
        );

        // Run with 60 days - SHOULD trigger
        $this->artisan('inventory:check-expiring-batches --days=60')
            ->assertSuccessful();

        Notification::assertSentTo(
            $this->adminUser,
            BatchExpiryNotification::class
        );
    }

    // ============================================
    // NOTIFICATION THROTTLING TESTS
    // ============================================

    /**
     * Test that notifications are not duplicated (throttled via cache).
     * 
     * If a low stock notification was sent recently (within cooldown period),
     * don't spam the user with the same notification.
     */
    public function test_notification_is_not_duplicated(): void
    {
        Notification::fake();

        // Create batch with stock near threshold
        $batch = Batch::create([
            'batch_number' => 'BATCH-THROTTLE-TEST',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $stockLocation = StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 25,
            'reserved_quantity' => 0,
        ]);

        $allocationService = app(BatchAllocationService::class);
        
        // First allocation triggers notification
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            2, // 25 - 2 = 23, still above min_stock
            null,
            $this->user,
            null
        );

        // Add more stock and allocate again to trigger low stock again
        $stockLocation->refresh();
        
        // Second allocation triggers low stock
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            5, // 23 - 5 = 18, now below min_stock
            null,
            $this->user,
            null
        );

        // This should trigger first notification
        Notification::assertSentToTimes($this->adminUser, LowStockNotification::class, 1);

        // Third allocation - should NOT trigger duplicate notification
        $allocationService->allocate(
            $this->product,
            $this->warehouse,
            3, // 18 - 3 = 15, still low but already notified
            null,
            $this->user,
            null
        );

        // Still only one notification (throttled)
        Notification::assertSentToTimes($this->adminUser, LowStockNotification::class, 1);
    }

    /**
     * Test throttling uses cache.
     */
    public function test_throttling_uses_cache(): void
    {
        $throttleService = app(NotificationThrottleService::class);
        
        $key = 'low_stock_' . $this->product->id . '_' . $this->warehouse->id;

        // Initially should allow notification
        $this->assertTrue($throttleService->shouldSendNotification($key));

        // Mark as sent
        $throttleService->markNotificationSent($key);

        // Now should be throttled
        $this->assertFalse($throttleService->shouldSendNotification($key));

        // Verify it's in cache
        $this->assertTrue(Cache::has('notification_throttle:' . $key));
    }

    /**
     * Test throttle expires after cooldown period.
     */
    public function test_throttle_expires_after_cooldown(): void
    {
        $throttleService = app(NotificationThrottleService::class);
        
        $key = 'low_stock_' . $this->product->id . '_' . $this->warehouse->id;

        // Mark as sent
        $throttleService->markNotificationSent($key, 1); // 1 minute cooldown

        // Should be throttled
        $this->assertFalse($throttleService->shouldSendNotification($key));

        // Travel forward in time (past cooldown)
        $this->travel(2)->minutes();

        // Should now allow notification
        $this->assertTrue($throttleService->shouldSendNotification($key));
    }

    /**
     * Test expiry notifications are also throttled.
     */
    public function test_expiry_notification_is_throttled(): void
    {
        Notification::fake();

        // Create expiring batch
        $batch = Batch::create([
            'batch_number' => 'BATCH-EXPIRY-THROTTLE',
            'product_id' => $this->product->id,
            'expiry_date' => Carbon::now()->addDays(15),
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $this->bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        // Run command twice
        $this->artisan('inventory:check-expiring-batches')->assertSuccessful();
        $this->artisan('inventory:check-expiring-batches')->assertSuccessful();

        // Should only have been notified once
        Notification::assertSentToTimes($this->adminUser, BatchExpiryNotification::class, 1);
    }
}
