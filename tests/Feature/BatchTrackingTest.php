<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use App\Models\WarehouseRack;
use App\Models\WarehouseBin;
use App\Models\Product;
use App\Models\Category;
use App\Models\Batch;
use App\Models\StockLocation;
use App\Models\AuditLog;
use App\Models\BatchMovement;
use App\Services\BatchInboundService;
use App\Services\BatchAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * TDD Tests for Batch/Lot Tracking
 * 
 * These tests are written FIRST before implementation.
 * Run: php artisan test --filter=BatchTrackingTest
 */
class BatchTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected WarehouseZone $storageZone;
    protected WarehouseRack $rack;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        
        $this->warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->storageZone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'STORAGE-A',
            'name' => 'Storage Zone A',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->rack = WarehouseRack::create([
            'zone_id' => $this->storageZone->id,
            'code' => 'RACK-01',
            'name' => 'Rack 01',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
        ]);

        $this->product = Product::create([
            'code' => 'PROD-001',
            'name' => 'Test Product',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'min_stock' => 10,
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FIFO',
        ]);
    }

    // ============================================
    // BATCH CREATION TESTS
    // ============================================

    public function test_batch_number_unique_per_product(): void
    {
        Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Batch::create([
            'batch_number' => 'BATCH-001', // Duplicate
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
    }

    public function test_batch_number_can_repeat_across_products(): void
    {
        $product2 = Product::create([
            'code' => 'PROD-002',
            'name' => 'Test Product 2',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FIFO',
        ]);

        $batch1 = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $batch2 = Batch::create([
            'batch_number' => 'BATCH-001', // Same batch number, different product
            'product_id' => $product2->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('batches', 2);
    }

    public function test_batch_can_span_multiple_bins(): void
    {
        $bin1 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 50,
            'is_active' => true,
        ]);

        $bin2 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-002',
            'max_capacity' => 50,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        // Place batch in multiple bins
        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin1->id,
            'quantity' => 30,
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin2->id,
            'quantity' => 20,
        ]);

        $this->assertEquals(50, $batch->total_quantity);
        $this->assertCount(2, $batch->stockLocations);
    }

    public function test_available_quantity_excludes_reserved(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'quantity' => 100,
            'reserved_quantity' => 25,
        ]);

        $this->assertEquals(100, $batch->total_quantity);
        $this->assertEquals(75, $batch->available_quantity);
    }

    // ============================================
    // FIFO/LIFO/FEFO ALLOCATION TESTS
    // ============================================

    public function test_fifo_allocates_oldest_batch_first(): void
    {
        // Create fully isolated infrastructure for this test to avoid interference
        $fifoZone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'FIFO-ZONE',
            'name' => 'FIFO Test Zone',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $fifoRack = WarehouseRack::create([
            'zone_id' => $fifoZone->id,
            'code' => 'FIFO-RACK',
            'name' => 'FIFO Test Rack',
            'is_active' => true,
        ]);

        $bin = WarehouseBin::create([
            'rack_id' => $fifoRack->id,
            'code' => 'FIFO-BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        // Create dedicated FIFO product
        $fifoProduct = Product::create([
            'code' => 'FIFO-PROD',
            'name' => 'FIFO Test Product',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'min_stock' => 10,
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FIFO',
        ]);

        // Create older batch - explicitly set created_at AFTER creation to bypass Eloquent overwrite
        $oldBatch = Batch::create([
            'batch_number' => 'BATCH-OLD',
            'product_id' => $fifoProduct->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        // Force older timestamp
        $oldBatch->timestamps = false;
        $oldBatch->created_at = now()->subDays(10);
        $oldBatch->save();
        $oldBatch->timestamps = true;
        StockLocation::create(['batch_id' => $oldBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        // Create newer batch - will have current timestamp
        $newBatch = Batch::create([
            'batch_number' => 'BATCH-NEW',
            'product_id' => $fifoProduct->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $newBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        $service = app(BatchAllocationService::class);
        $allocations = $service->allocate($fifoProduct, $this->warehouse, 30);

        // FIFO: Should allocate from oldest batch first
        $this->assertNotNull($allocations->first(), 'Allocations should not be empty');
        $this->assertEquals($oldBatch->id, $allocations->first()['batch']->id);
        $this->assertEquals(30, $allocations->first()['quantity']);
    }

    public function test_fefo_allocates_earliest_expiry_first(): void
    {
        // Create fully isolated infrastructure for this test
        $fefoZone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'FEFO-ZONE',
            'name' => 'FEFO Test Zone',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $fefoRack = WarehouseRack::create([
            'zone_id' => $fefoZone->id,
            'code' => 'FEFO-RACK',
            'name' => 'FEFO Test Rack',
            'is_active' => true,
        ]);

        $bin = WarehouseBin::create([
            'rack_id' => $fefoRack->id,
            'code' => 'FEFO-BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        // Create a dedicated FEFO product
        $fefoProduct = Product::create([
            'code' => 'FEFO-PROD',
            'name' => 'FEFO Test Product',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'min_stock' => 10,
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FEFO',
        ]);

        // Batch expiring sooner - create FIRST to prove FEFO ordering works by expiry_date
        $soonerBatch = Batch::create([
            'batch_number' => 'BATCH-SOONER',
            'product_id' => $fefoProduct->id,
            'cost_price' => 100,
            'status' => 'active',
            'expiry_date' => now()->addMonths(1),
        ]);
        StockLocation::create(['batch_id' => $soonerBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        // Batch expiring later - created SECOND but should be allocated LAST in FEFO
        $laterBatch = Batch::create([
            'batch_number' => 'BATCH-LATER',
            'product_id' => $fefoProduct->id,
            'cost_price' => 100,
            'status' => 'active',
            'expiry_date' => now()->addMonths(6),
        ]);
        StockLocation::create(['batch_id' => $laterBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        $service = app(BatchAllocationService::class);
        $allocations = $service->allocate($fefoProduct, $this->warehouse, 30);

        // FEFO: Should allocate from batch expiring soonest
        $this->assertNotNull($allocations->first(), 'Allocations should not be empty');
        $this->assertEquals($soonerBatch->id, $allocations->first()['batch']->id);
    }

    public function test_fefo_skips_expired_batches(): void
    {
        // Create fully isolated infrastructure for this test
        $fefoZone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'FEFO-SKIP-ZONE',
            'name' => 'FEFO Skip Test Zone',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $fefoRack = WarehouseRack::create([
            'zone_id' => $fefoZone->id,
            'code' => 'FEFO-SKIP-RACK',
            'name' => 'FEFO Skip Test Rack',
            'is_active' => true,
        ]);

        $bin = WarehouseBin::create([
            'rack_id' => $fefoRack->id,
            'code' => 'FEFO-SKIP-BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        // Create a dedicated FEFO product
        $fefoProduct = Product::create([
            'code' => 'FEFO-SKIP-PROD',
            'name' => 'FEFO Skip Test Product',
            'category_id' => $this->category->id,
            'unit' => 'pcs',
            'min_stock' => 10,
            'purchase_price' => 100,
            'selling_price' => 150,
            'status' => true,
            'enable_batch_tracking' => true,
            'batch_method' => 'FEFO',
        ]);

        // Expired batch
        $expiredBatch = Batch::create([
            'batch_number' => 'BATCH-EXPIRED',
            'product_id' => $fefoProduct->id,
            'cost_price' => 100,
            'status' => 'expired',
            'expiry_date' => now()->subDays(5),
        ]);
        StockLocation::create(['batch_id' => $expiredBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        // Valid batch
        $validBatch = Batch::create([
            'batch_number' => 'BATCH-VALID',
            'product_id' => $fefoProduct->id,
            'cost_price' => 100,
            'status' => 'active',
            'expiry_date' => now()->addMonths(1),
        ]);
        StockLocation::create(['batch_id' => $validBatch->id, 'bin_id' => $bin->id, 'quantity' => 50, 'reserved_quantity' => 0]);

        $service = app(BatchAllocationService::class);
        $allocations = $service->allocate($fefoProduct, $this->warehouse, 30);

        // Should skip expired batch
        $this->assertNotNull($allocations->first(), 'Allocations should not be empty');
        $this->assertEquals($validBatch->id, $allocations->first()['batch']->id);
    }

    public function test_allocation_throws_when_insufficient_stock(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch->id, 'bin_id' => $bin->id, 'quantity' => 10]);

        $this->expectException(\App\Exceptions\InsufficientStockException::class);

        $service = app(BatchAllocationService::class);
        $service->allocate($this->product, $this->warehouse, 50); // Request more than available
    }

    // ============================================
    // PUTAWAY (INBOUND) TESTS - Consolidation & Spillover
    // ============================================

    public function test_putaway_consolidates_to_existing_bin_with_same_batch(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        // Existing batch in bin with 30 units
        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch->id, 'bin_id' => $bin->id, 'quantity' => 30]);

        $service = app(BatchInboundService::class);
        
        // Add 20 more units of SAME batch
        $result = $service->putaway($batch, $this->warehouse, 20);

        // Should consolidate into existing bin
        $this->assertCount(1, $batch->fresh()->stockLocations);
        $this->assertEquals(50, $batch->fresh()->total_quantity);
        $this->assertEquals($bin->id, $result->first()['bin']->id);
    }

    public function test_putaway_finds_new_bin_when_batch_is_new(): void
    {
        $bin1 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $bin2 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-002',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        // New batch (no existing stock location)
        $batch = Batch::create([
            'batch_number' => 'BATCH-NEW',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $service = app(BatchInboundService::class);
        $result = $service->putaway($batch, $this->warehouse, 50);

        // Should create new stock location in an empty bin
        $this->assertCount(1, $batch->fresh()->stockLocations);
        $this->assertEquals(50, $batch->fresh()->total_quantity);
    }

    public function test_putaway_respects_bin_capacity_limit(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 50, // Limited capacity
            'is_active' => true,
        ]);

        // Second bin for spillover
        $bin2 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-002',
            'max_capacity' => 50,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch->id, 'bin_id' => $bin->id, 'quantity' => 40, 'reserved_quantity' => 0]);

        $service = app(BatchInboundService::class);
        
        // Try to add 20 units, but only 10 capacity remaining in bin1
        // Should fill bin1 to 50, spillover 10 to bin2
        $result = $service->putaway($batch, $this->warehouse, 20);

        $stockLocation = StockLocation::where('batch_id', $batch->id)
            ->where('bin_id', $bin->id)
            ->first();

        // Original bin should be at capacity (50), not overfilled
        $this->assertLessThanOrEqual(50, $stockLocation->quantity);
        
        // Total quantity should be 60 (40 + 20)
        $this->assertEquals(60, $batch->fresh()->total_quantity);
    }

    public function test_putaway_spillover_splits_across_bins(): void
    {
        // Bin 1 with limited capacity
        $bin1 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 30,
            'is_active' => true,
        ]);

        // Bin 2 for spillover
        $bin2 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-002',
            'max_capacity' => 30,
            'is_active' => true,
        ]);

        // Bin 3 for more spillover
        $bin3 = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-003',
            'max_capacity' => 30,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $service = app(BatchInboundService::class);
        
        // Try to putaway 70 units - needs spillover across bins
        $result = $service->putaway($batch, $this->warehouse, 70);

        // Should be split: 30 + 30 + 10 = 70
        $this->assertEquals(70, $batch->fresh()->total_quantity);
        $this->assertGreaterThanOrEqual(2, $batch->fresh()->stockLocations->count());
    }

    public function test_putaway_throws_when_no_capacity_available(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 10,
            'is_active' => true,
        ]);

        // Fill the only bin
        $existingBatch = Batch::create([
            'batch_number' => 'BATCH-OLD',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $existingBatch->id, 'bin_id' => $bin->id, 'quantity' => 10]);

        $newBatch = Batch::create([
            'batch_number' => 'BATCH-NEW',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $this->expectException(\App\Exceptions\InsufficientCapacityException::class);

        $service = app(BatchInboundService::class);
        $service->putaway($newBatch, $this->warehouse, 50);
    }

    // ============================================
    // BATCH MOVEMENT AUDIT TRAIL TESTS
    // ============================================

    public function test_stock_in_creates_batch_movement_record(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $service = app(BatchInboundService::class);
        $service->putaway($batch, $this->warehouse, 50, $this->user);

        $this->assertDatabaseHas('batch_movements', [
            'batch_id' => $batch->id,
            'movement_type' => 'stock_in',
            'quantity' => 50,
            'quantity_before' => 0,
            'quantity_after' => 50,
        ]);
    }

    public function test_stock_out_creates_negative_movement_record(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch->id, 'bin_id' => $bin->id, 'quantity' => 50]);

        $service = app(BatchAllocationService::class);
        $service->allocate($this->product, $this->warehouse, 20, null, $this->user);

        $this->assertDatabaseHas('batch_movements', [
            'batch_id' => $batch->id,
            'movement_type' => 'stock_out',
        ]);
    }

    // ============================================
    // BIN OCCUPANCY INTEGRATION TESTS
    // ============================================

    public function test_bin_current_occupancy_reflects_stock_locations(): void
    {
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch1 = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch1->id, 'bin_id' => $bin->id, 'quantity' => 30]);

        $batch2 = Batch::create([
            'batch_number' => 'BATCH-002',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        StockLocation::create(['batch_id' => $batch2->id, 'bin_id' => $bin->id, 'quantity' => 20]);

        // Bin should report total occupancy from all stock locations
        $this->assertEquals(50, $bin->fresh()->current_occupancy);
        $this->assertTrue($bin->fresh()->hasCapacity(50)); // 50 remaining
        $this->assertFalse($bin->fresh()->hasCapacity(51)); // Would exceed
    }

    // ============================================
    // API TESTS (Phase D)
    // ============================================

    /**
     * Test API stock-in creates batch, stock location, and audit log.
     */
    public function test_api_stock_in_creates_batch_and_audit_log(): void
    {
        // Create a bin for stock placement
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-API-IN',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/stock-in', [
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25,
                    'purchase_price' => 100,
                ],
            ],
            'notes' => 'API Test Stock In',
        ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Stock in created successfully',
            ]);

        // Verify batch was created
        $this->assertDatabaseHas('batches', [
            'product_id' => $this->product->id,
        ]);

        // Verify stock location was created
        $batch = Batch::where('product_id', $this->product->id)->first();
        $this->assertNotNull($batch);
        $this->assertDatabaseHas('stock_locations', [
            'batch_id' => $batch->id,
        ]);

        // Verify audit log was created for the batch
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Batch::class,
            'auditable_id' => $batch->id,
            'event' => 'created',
        ]);

        // Verify batch movement was created
        $this->assertDatabaseHas('batch_movements', [
            'batch_id' => $batch->id,
            'movement_type' => 'stock_in',
        ]);
    }

    /**
     * Test API stock-out uses allocation service and creates audit log.
     */
    public function test_api_stock_out_uses_allocation_service(): void
    {
        // Setup: Create bin and existing batch with stock
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-API-OUT',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-API-OUT',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/stock-out', [
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'selling_price' => 150,
                ],
            ],
            'customer' => 'API Test Customer',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Stock out created successfully',
            ]);

        // Verify stock was reduced
        $stockLocation = StockLocation::where('batch_id', $batch->id)->first();
        $this->assertEquals(30, $stockLocation->quantity); // 50 - 20 = 30

        // Verify batch movement was created for stock out
        $this->assertDatabaseHas('batch_movements', [
            'batch_id' => $batch->id,
            'movement_type' => 'stock_out',
        ]);
    }

    /**
     * Test API get batches returns inventory data.
     */
    public function test_api_get_batches_returns_inventory(): void
    {
        // Create bin and batch with stock
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-API-LIST',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-API-LIST',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'quantity' => 75,
            'reserved_quantity' => 10,
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson('/api/v1/inventory/batches');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify response contains batch data
        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData);
        
        $foundBatch = collect($responseData)->firstWhere('batch_number', 'BATCH-API-LIST');
        $this->assertNotNull($foundBatch);
        $this->assertEquals(75, $foundBatch['total_quantity']);
        $this->assertEquals(10, $foundBatch['reserved_quantity']);
        $this->assertEquals(65, $foundBatch['available_quantity']); // 75 - 10
    }

    /**
     * Test API stock-out returns error for insufficient stock.
     */
    public function test_api_stock_out_returns_error_for_insufficient_stock(): void
    {
        // Setup: Create bin and existing batch with limited stock
        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-API-ERR',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'batch_number' => 'BATCH-LIMITED',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
        ]);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/v1/stock-out', [
            'warehouse_id' => $this->warehouse->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 50, // More than available
                    'selling_price' => 150,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'insufficient_stock',
            ]);
    }
}
