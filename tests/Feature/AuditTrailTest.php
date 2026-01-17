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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * TDD Tests for Global Audit Trail
 * 
 * These tests are written FIRST before implementation.
 * Run: php artisan test --filter=AuditTrailTest
 * 
 * The Audit Trail system provides:
 * - Polymorphic logging (auditable_type, auditable_id)
 * - Automatic logging via LogsActivity trait
 * - Data delta (old_values, new_values - only changed fields)
 * - Context capture (user_id, ip_address, url)
 */
class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected WarehouseZone $zone;
    protected WarehouseRack $rack;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        $this->warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'WH-TEST',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
            'is_active' => true,
        ]);

        $this->rack = WarehouseRack::create([
            'zone_id' => $this->zone->id,
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
    // AUDIT LOG CREATION TESTS
    // ============================================

    /**
     * Test that creating a Batch automatically creates an audit log entry.
     * 
     * Verifies:
     * - AuditLog record exists
     * - Event is 'created'
     * - auditable_type is 'App\Models\Batch'
     * - auditable_id matches the batch ID
     * - new_values contains the created data
     * - old_values is null (no previous state for create)
     */
    public function test_creating_a_batch_automatically_creates_audit_log(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log should exist for batch creation');
        $this->assertEquals('created', $auditLog->event);
        $this->assertEquals(Batch::class, $auditLog->auditable_type);
        $this->assertEquals($batch->id, $auditLog->auditable_id);
        $this->assertNull($auditLog->old_values, 'old_values should be null for create event');
        $this->assertNotNull($auditLog->new_values, 'new_values should contain created data');
        
        $newValues = json_decode($auditLog->new_values, true);
        $this->assertEquals('BATCH-001', $newValues['batch_number']);
    }

    /**
     * Test that updating a Batch records only the changed fields.
     * 
     * Verifies:
     * - Only modified fields appear in old_values and new_values
     * - Unchanged fields are NOT recorded (to save storage space)
     */
    public function test_updating_a_batch_records_only_changed_fields(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-001',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
            'notes' => 'Original notes',
        ]);

        // Clear any creation audit logs from setup
        AuditLog::where('event', 'created')->delete();

        // Update only the cost_price
        $batch->update(['cost_price' => 150]);

        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log should exist for batch update');
        $this->assertEquals('updated', $auditLog->event);

        $oldValues = json_decode($auditLog->old_values, true);
        $newValues = json_decode($auditLog->new_values, true);

        // Should only contain cost_price, not batch_number, status, notes, etc.
        $this->assertArrayHasKey('cost_price', $oldValues);
        $this->assertArrayHasKey('cost_price', $newValues);
        $this->assertEquals(100, $oldValues['cost_price']);
        $this->assertEquals(150, $newValues['cost_price']);

        // Should NOT contain unchanged fields
        $this->assertArrayNotHasKey('batch_number', $oldValues);
        $this->assertArrayNotHasKey('status', $oldValues);
        $this->assertArrayNotHasKey('notes', $oldValues);
    }

    /**
     * Test that deleting a Batch creates an audit log that persists.
     * 
     * Verifies:
     * - Audit log exists with event 'deleted'
     * - old_values contains the deleted record's data
     * - Audit log persists even after parent record is deleted
     */
    public function test_deleting_a_batch_records_audit_log(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-TO-DELETE',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);
        $batchId = $batch->id;

        // Delete the batch
        $batch->delete();

        // Verify batch is deleted
        $this->assertNull(Batch::find($batchId));

        // Audit log should still exist
        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batchId)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($auditLog, 'Audit log should persist after batch deletion');
        $this->assertEquals('deleted', $auditLog->event);
        
        $oldValues = json_decode($auditLog->old_values, true);
        $this->assertEquals('BATCH-TO-DELETE', $oldValues['batch_number']);
    }

    // ============================================
    // USER CONTEXT TESTS
    // ============================================

    /**
     * Test that the audit log captures the authenticated user's ID.
     */
    public function test_audit_log_captures_authenticated_user_id(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-USER-TEST',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->user->id, $auditLog->user_id);
    }

    /**
     * Test that audit log is created with null user_id when not authenticated.
     * This handles system-level changes or background jobs.
     */
    public function test_audit_log_works_without_authenticated_user(): void
    {
        // Ensure no user is logged in
        Auth::logout();

        $batch = Batch::create([
            'batch_number' => 'BATCH-NO-USER',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNull($auditLog->user_id);
    }

    // ============================================
    // POLYMORPHIC TESTS (Multiple Models)
    // ============================================

    /**
     * Test that StockLocation changes are audited.
     */
    public function test_stock_location_changes_are_audited(): void
    {
        $this->actingAs($this->user);

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

        $stockLocation = StockLocation::create([
            'batch_id' => $batch->id,
            'bin_id' => $bin->id,
            'quantity' => 50,
            'reserved_quantity' => 0,
        ]);

        $auditLog = AuditLog::where('auditable_type', StockLocation::class)
            ->where('auditable_id', $stockLocation->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertEquals($this->user->id, $auditLog->user_id);
    }

    /**
     * Test that WarehouseBin changes are audited.
     */
    public function test_warehouse_bin_changes_are_audited(): void
    {
        $this->actingAs($this->user);

        $bin = WarehouseBin::create([
            'rack_id' => $this->rack->id,
            'code' => 'BIN-AUDIT',
            'max_capacity' => 100,
            'is_active' => true,
        ]);

        $auditLog = AuditLog::where('auditable_type', WarehouseBin::class)
            ->where('auditable_id', $bin->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($auditLog);

        // Update the bin
        $bin->update(['max_capacity' => 200]);

        $updateLog = AuditLog::where('auditable_type', WarehouseBin::class)
            ->where('auditable_id', $bin->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($updateLog);
        
        $oldValues = json_decode($updateLog->old_values, true);
        $newValues = json_decode($updateLog->new_values, true);
        
        $this->assertEquals(100, $oldValues['max_capacity']);
        $this->assertEquals(200, $newValues['max_capacity']);
    }

    // ============================================
    // AUDIT LOG QUERY TESTS
    // ============================================

    /**
     * Test that we can retrieve audit logs for a specific model.
     */
    public function test_can_retrieve_audit_logs_for_model(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-HISTORY',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $batch->update(['cost_price' => 150]);
        $batch->update(['cost_price' => 200]);

        // Get all audit logs for this batch
        $auditLogs = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->orderBy('created_at', 'asc')
            ->get();

        $this->assertCount(3, $auditLogs); // 1 create + 2 updates
        $this->assertEquals('created', $auditLogs[0]->event);
        $this->assertEquals('updated', $auditLogs[1]->event);
        $this->assertEquals('updated', $auditLogs[2]->event);
    }

    /**
     * Test that we can filter audit logs by user.
     */
    public function test_can_filter_audit_logs_by_user(): void
    {
        $user1 = User::factory()->create(['role' => 'admin']);
        $user2 = User::factory()->create(['role' => 'staff']);

        // User 1 creates a batch
        $this->actingAs($user1);
        $batch = Batch::create([
            'batch_number' => 'BATCH-USER1',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        // User 2 updates the batch
        $this->actingAs($user2);
        $batch->update(['cost_price' => 150]);

        $user1Logs = AuditLog::where('user_id', $user1->id)->count();
        $user2Logs = AuditLog::where('user_id', $user2->id)->count();

        $this->assertGreaterThanOrEqual(1, $user1Logs);
        $this->assertGreaterThanOrEqual(1, $user2Logs);
    }

    // ============================================
    // EDGE CASES
    // ============================================

    /**
     * Test that mass updates are audited correctly.
     */
    public function test_mass_update_is_audited(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-MASS',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
            'notes' => 'Original',
        ]);

        // Mass update multiple fields
        $batch->update([
            'cost_price' => 200,
            'notes' => 'Updated notes',
        ]);

        $auditLog = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->where('event', 'updated')
            ->first();

        $oldValues = json_decode($auditLog->old_values, true);
        $newValues = json_decode($auditLog->new_values, true);

        // Both changed fields should be present
        $this->assertArrayHasKey('cost_price', $oldValues);
        $this->assertArrayHasKey('notes', $oldValues);
        $this->assertEquals(100, $oldValues['cost_price']);
        $this->assertEquals('Original', $oldValues['notes']);
        $this->assertEquals(200, $newValues['cost_price']);
        $this->assertEquals('Updated notes', $newValues['notes']);
    }

    /**
     * Test that no audit log is created when update makes no changes.
     */
    public function test_no_audit_log_when_no_changes(): void
    {
        $this->actingAs($this->user);

        $batch = Batch::create([
            'batch_number' => 'BATCH-NOCHANGE',
            'product_id' => $this->product->id,
            'cost_price' => 100,
            'status' => 'active',
        ]);

        $initialCount = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->count();

        // Update with same values (no actual change)
        $batch->update(['cost_price' => 100]);

        $finalCount = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->count();

        // Should not create additional audit log since nothing changed
        $this->assertEquals($initialCount, $finalCount);
    }
}
