<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use App\Models\WarehouseRack;
use App\Models\WarehouseBin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TDD Tests for Location Management (Zone/Rack/Bin)
 * 
 * These tests are written FIRST before implementation.
 * Run: php artisan test --filter=LocationManagementTest
 */
class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;

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
    }

    // ============================================
    // HIERARCHY TESTS
    // ============================================

    public function test_zone_belongs_to_warehouse(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $this->assertInstanceOf(Warehouse::class, $zone->warehouse);
        $this->assertEquals($this->warehouse->id, $zone->warehouse->id);
    }

    public function test_rack_belongs_to_zone(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
            'name' => 'Rack 01',
        ]);

        $this->assertInstanceOf(WarehouseZone::class, $rack->zone);
        $this->assertEquals($zone->id, $rack->zone->id);
    }

    public function test_bin_belongs_to_rack(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
        ]);

        $bin = WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001',
            'level' => 1,
        ]);

        $this->assertInstanceOf(WarehouseRack::class, $bin->rack);
        $this->assertEquals($rack->id, $bin->rack->id);
    }

    public function test_bins_accessible_through_zone(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
        ]);

        $bin1 = WarehouseBin::create(['rack_id' => $rack->id, 'code' => 'BIN-001']);
        $bin2 = WarehouseBin::create(['rack_id' => $rack->id, 'code' => 'BIN-002']);

        $bins = $zone->bins;

        $this->assertCount(2, $bins);
        $this->assertTrue($bins->contains($bin1));
        $this->assertTrue($bins->contains($bin2));
    }

    public function test_full_path_returns_correct_format(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
        ]);

        $bin = WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001',
        ]);

        // Expected format: "WH-MAIN/ZONE-A/RACK-01/BIN-001"
        $this->assertEquals(
            'WH-MAIN/ZONE-A/RACK-01/BIN-001',
            $bin->full_path
        );
    }

    // ============================================
    // VALIDATION TESTS
    // ============================================

    public function test_zone_code_unique_within_warehouse(): void
    {
        WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A', // Duplicate code in same warehouse
            'name' => 'Zone A Duplicate',
            'type' => 'storage',
        ]);
    }

    public function test_zone_code_can_repeat_across_warehouses(): void
    {
        $warehouse2 = Warehouse::create([
            'name' => 'Second Warehouse',
            'code' => 'WH-02',
            'is_active' => true,
        ]);

        $zone1 = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A in WH1',
            'type' => 'storage',
        ]);

        $zone2 = WarehouseZone::create([
            'warehouse_id' => $warehouse2->id,
            'code' => 'ZONE-A', // Same code, different warehouse - OK
            'name' => 'Zone A in WH2',
            'type' => 'storage',
        ]);

        $this->assertDatabaseCount('warehouse_zones', 2);
        $this->assertEquals($zone1->code, $zone2->code);
    }

    public function test_rack_code_unique_within_zone(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01', // Duplicate code in same zone
        ]);
    }

    public function test_bin_code_unique_within_rack(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create([
            'zone_id' => $zone->id,
            'code' => 'RACK-01',
        ]);

        WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001', // Duplicate code in same rack
        ]);
    }

    public function test_bin_barcode_globally_unique(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack1 = WarehouseRack::create(['zone_id' => $zone->id, 'code' => 'RACK-01']);
        $rack2 = WarehouseRack::create(['zone_id' => $zone->id, 'code' => 'RACK-02']);

        WarehouseBin::create([
            'rack_id' => $rack1->id,
            'code' => 'BIN-001',
            'barcode' => 'BARCODE-12345',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        WarehouseBin::create([
            'rack_id' => $rack2->id,
            'code' => 'BIN-002',
            'barcode' => 'BARCODE-12345', // Same barcode, even in different rack - NOT OK
        ]);
    }

    public function test_zone_type_must_be_valid_enum(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-X',
            'name' => 'Invalid Zone',
            'type' => 'invalid_type', // Not in enum
        ]);
    }

    // ============================================
    // CAPACITY TESTS
    // ============================================

    public function test_bin_with_null_capacity_allows_unlimited(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create(['zone_id' => $zone->id, 'code' => 'RACK-01']);

        $bin = WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001',
            'max_capacity' => null, // Unlimited
        ]);

        // Should always return true for any quantity
        $this->assertTrue($bin->hasCapacity(1));
        $this->assertTrue($bin->hasCapacity(1000));
        $this->assertTrue($bin->hasCapacity(999999));
    }

    public function test_bin_reports_has_capacity_correctly(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create(['zone_id' => $zone->id, 'code' => 'RACK-01']);

        $bin = WarehouseBin::create([
            'rack_id' => $rack->id,
            'code' => 'BIN-001',
            'max_capacity' => 100,
        ]);

        // Initially empty, should have capacity
        $this->assertTrue($bin->hasCapacity(50));
        $this->assertTrue($bin->hasCapacity(100));
        $this->assertFalse($bin->hasCapacity(101));
    }

    // ============================================
    // SCOPE & SOFT BEHAVIOR TESTS
    // ============================================

    public function test_inactive_zone_excludes_from_active_scope(): void
    {
        WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Active Zone',
            'type' => 'storage',
            'is_active' => true,
        ]);

        WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-B',
            'name' => 'Inactive Zone',
            'type' => 'storage',
            'is_active' => false,
        ]);

        $activeZones = WarehouseZone::active()->get();

        $this->assertCount(1, $activeZones);
        $this->assertEquals('ZONE-A', $activeZones->first()->code);
    }

    public function test_deleting_zone_cascades_to_racks_and_bins(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $rack = WarehouseRack::create(['zone_id' => $zone->id, 'code' => 'RACK-01']);
        $bin = WarehouseBin::create(['rack_id' => $rack->id, 'code' => 'BIN-001']);

        $this->assertDatabaseHas('warehouse_zones', ['id' => $zone->id]);
        $this->assertDatabaseHas('warehouse_racks', ['id' => $rack->id]);
        $this->assertDatabaseHas('warehouse_bins', ['id' => $bin->id]);

        $zone->delete();

        $this->assertDatabaseMissing('warehouse_zones', ['id' => $zone->id]);
        $this->assertDatabaseMissing('warehouse_racks', ['id' => $rack->id]);
        $this->assertDatabaseMissing('warehouse_bins', ['id' => $bin->id]);
    }

    // ============================================
    // HTTP CONTROLLER TESTS
    // ============================================

    public function test_authenticated_user_can_view_zones_index(): void
    {
        $zone = WarehouseZone::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-A',
            'name' => 'Zone A',
            'type' => 'storage',
        ]);

        $response = $this->actingAs($this->user)->get(route('zones.index'));

        $response->assertStatus(200);
        $response->assertSee('Zone A');
    }

    public function test_authenticated_user_can_create_zone(): void
    {
        $response = $this->actingAs($this->user)->post(route('zones.store'), [
            'warehouse_id' => $this->warehouse->id,
            'code' => 'ZONE-NEW',
            'name' => 'New Zone',
            'type' => 'storage',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('warehouse_zones', [
            'code' => 'ZONE-NEW',
            'name' => 'New Zone',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_zones(): void
    {
        $response = $this->get(route('zones.index'));

        $response->assertRedirect(route('login'));
    }
}
