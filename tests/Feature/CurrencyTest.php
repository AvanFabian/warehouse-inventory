<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * TDD Tests for Multi-Currency Support (Phase F)
 * 
 * Tests currency handling, exchange rates, and transaction fee calculations.
 * Base Currency: IDR (1.00000000)
 */
class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Warehouse $warehouse;
    protected Supplier $supplier;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
        
        $this->warehouse = Warehouse::create([
            'name' => 'Currency Test Warehouse',
            'code' => 'WH-CURR',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'International Supplier',
            'code' => 'SUP-INT',
            'contact_person' => 'John Doe',
            'phone' => '+1-555-0100',
            'email' => 'supplier@example.com',
            'address' => '123 Export Street',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'name' => 'International Customer',
            'code' => 'CUST-INT',
            'contact_person' => 'Jane Doe',
            'phone' => '+1-555-0200',
            'email' => 'customer@example.com',
            'address' => '456 Import Avenue',
        ]);

        // Seed base currencies
        Currency::create([
            'code' => 'IDR',
            'name' => 'Indonesian Rupiah',
            'symbol' => 'Rp',
            'is_base' => true,
            'exchange_rate' => 1.00000000,
        ]);

        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_base' => false,
            'exchange_rate' => 15850.00000000, // 1 USD = 15,850 IDR
        ]);
    }

    // ============================================
    // CURRENCY RATE TESTS
    // ============================================

    /**
     * Test that the system can fetch and store latest exchange rates.
     */
    public function test_can_fetch_and_store_latest_rates(): void
    {
        // Mock the external API response
        Http::fake([
            'api.exchangerate-api.com/*' => Http::response([
                'result' => 'success',
                'base_code' => 'USD',
                'conversion_rates' => [
                    'IDR' => 15900.50,
                    'USD' => 1,
                    'EUR' => 0.92,
                ],
            ], 200),
        ]);

        $service = app(CurrencyService::class);
        
        // Fetch and update rates
        $result = $service->fetchLatestRates();

        $this->assertTrue($result);

        // Verify USD rate was updated
        $usd = Currency::where('code', 'USD')->first();
        $this->assertEquals(15900.50, $usd->exchange_rate);

        // IDR should remain 1.00 as base currency
        $idr = Currency::where('code', 'IDR')->first();
        $this->assertEquals(1.00000000, $idr->exchange_rate);
    }

    /**
     * Test conversion logic is accurate.
     */
    public function test_conversion_logic_is_accurate(): void
    {
        $service = app(CurrencyService::class);

        // Test USD to IDR conversion
        // 1000 USD * 15850 = 15,850,000 IDR
        $result = $service->convert(1000, 'USD', 'IDR');
        $this->assertEquals(15850000, $result);

        // Test IDR to USD conversion
        // 15,850,000 IDR / 15850 = 1000 USD
        $result = $service->convert(15850000, 'IDR', 'USD');
        $this->assertEquals(1000, $result);

        // Test same currency (no conversion)
        $result = $service->convert(5000, 'IDR', 'IDR');
        $this->assertEquals(5000, $result);
    }

    /**
     * Test that small amounts convert correctly (precision test).
     */
    public function test_conversion_handles_small_amounts(): void
    {
        $service = app(CurrencyService::class);

        // Test 1 USD = 15850 IDR
        $result = $service->convert(1, 'USD', 'IDR');
        $this->assertEquals(15850, $result);

        // Test fractional conversion
        // 0.50 USD = 7925 IDR
        $result = $service->convert(0.50, 'USD', 'IDR');
        $this->assertEquals(7925, $result);
    }

    // ============================================
    // TRANSACTION RATE LOCK TESTS
    // ============================================

    /**
     * Test that transaction saves historical exchange rate.
     */
    public function test_transaction_saves_historical_rate(): void
    {
        $this->actingAs($this->user);

        // Create a sales order with USD currency
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-CURR-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'draft',
            'currency_code' => 'USD',
            'subtotal' => 1000.00, // $1,000 USD
            'total' => 1000.00,
            'created_by' => $this->user->id,
        ]);

        // Verify exchange rate was locked at transaction time
        $this->assertNotNull($salesOrder->exchange_rate_at_transaction);
        $this->assertEquals(15850.00000000, $salesOrder->exchange_rate_at_transaction);

        // Now update the current USD rate
        Currency::where('code', 'USD')->update(['exchange_rate' => 16000.00]);

        // The order's locked rate should NOT change
        $salesOrder->refresh();
        $this->assertEquals(15850.00000000, $salesOrder->exchange_rate_at_transaction);

        // Verify we can calculate the IDR equivalent using the locked rate
        $idrEquivalent = $salesOrder->total * $salesOrder->exchange_rate_at_transaction;
        $this->assertEquals(15850000, $idrEquivalent);
    }

    /**
     * Test purchase order also locks exchange rate.
     */
    public function test_purchase_order_saves_historical_rate(): void
    {
        $this->actingAs($this->user);

        $purchaseOrder = PurchaseOrder::create([
            'po_number' => 'PO-CURR-001',
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'draft',
            'currency_code' => 'USD',
            'total_amount' => 5000.00, // $5,000 USD
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(15850.00000000, $purchaseOrder->exchange_rate_at_transaction);
    }

    // ============================================
    // TRANSACTION FEES & NET AMOUNT TESTS
    // ============================================

    /**
     * Test order calculates net amount correctly.
     * 
     * Net Amount = Total Amount - Transaction Fees
     */
    public function test_order_calculates_net_amount_correctly(): void
    {
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-FEE-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'draft',
            'currency_code' => 'USD',
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'transaction_fees' => 25.00, // $25 bank fee
            'fee_currency_code' => 'USD',
            'created_by' => $this->user->id,
        ]);

        // Net Amount should be automatically calculated
        $this->assertEquals(975.00, $salesOrder->net_amount); // 1000 - 25 = 975
    }

    /**
     * Test net amount updates when fees change.
     */
    public function test_net_amount_updates_when_fees_change(): void
    {
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-FEE-002',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'draft',
            'currency_code' => 'USD',
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'transaction_fees' => 50.00,
            'fee_currency_code' => 'USD',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(1950.00, $salesOrder->net_amount);

        // Update fees
        $salesOrder->update(['transaction_fees' => 75.00]);
        
        $this->assertEquals(1925.00, $salesOrder->net_amount); // 2000 - 75 = 1925
    }

    /**
     * Test transaction fees conversion for expense reporting.
     * 
     * Scenario: We want to report total bank fees paid in IDR over a period.
     */
    public function test_transaction_fees_conversion(): void
    {
        // Create multiple orders with fees in USD
        SalesOrder::create([
            'so_number' => 'SO-REPORT-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'delivered',
            'currency_code' => 'USD',
            'subtotal' => 1000.00,
            'total' => 1000.00,
            'transaction_fees' => 25.00,
            'fee_currency_code' => 'USD',
            'exchange_rate_at_transaction' => 15850.00,
            'created_by' => $this->user->id,
        ]);

        SalesOrder::create([
            'so_number' => 'SO-REPORT-002',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'delivered',
            'currency_code' => 'USD',
            'subtotal' => 2000.00,
            'total' => 2000.00,
            'transaction_fees' => 40.00,
            'fee_currency_code' => 'USD',
            'exchange_rate_at_transaction' => 16000.00, // Different rate
            'created_by' => $this->user->id,
        ]);

        $service = app(CurrencyService::class);

        // Calculate total fees in IDR using historical rates
        $totalFeesInIdr = SalesOrder::whereNotNull('transaction_fees')
            ->where('fee_currency_code', 'USD')
            ->get()
            ->sum(function ($order) {
                return $order->transaction_fees * $order->exchange_rate_at_transaction;
            });

        // Order 1: 25 USD * 15850 = 396,250 IDR
        // Order 2: 40 USD * 16000 = 640,000 IDR
        // Total: 1,036,250 IDR
        $this->assertEquals(1036250, $totalFeesInIdr);
    }

    /**
     * Test fees in different currency than transaction.
     */
    public function test_fee_in_different_currency(): void
    {
        // Order is in USD, but bank charges fee in IDR
        $salesOrder = SalesOrder::create([
            'so_number' => 'SO-MIXED-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now(),
            'status' => 'draft',
            'currency_code' => 'USD',
            'subtotal' => 500.00,
            'total' => 500.00,
            'transaction_fees' => 100000.00, // IDR 100,000 fee
            'fee_currency_code' => 'IDR',
            'exchange_rate_at_transaction' => 15850.00,
            'created_by' => $this->user->id,
        ]);

        $service = app(CurrencyService::class);

        // Get fee in order currency (USD)
        $feeInOrderCurrency = $service->convertUsingRate(
            100000, 
            'IDR', 
            'USD', 
            $salesOrder->exchange_rate_at_transaction
        );

        // 100,000 IDR / 15850 = approximately 6.31 USD
        $this->assertEqualsWithDelta(6.31, $feeInOrderCurrency, 0.01);

        // Net amount should account for the fee in order currency
        // This depends on your business logic
        $this->assertNotNull($salesOrder->net_amount);
    }

    // ============================================
    // CURRENCY MANAGEMENT TESTS
    // ============================================

    /**
     * Test creating a new currency.
     */
    public function test_can_create_currency(): void
    {
        $currency = Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'is_base' => false,
            'exchange_rate' => 17200.00, // 1 EUR = 17,200 IDR
        ]);

        $this->assertDatabaseHas('currencies', [
            'code' => 'EUR',
            'is_base' => false,
        ]);
    }

    /**
     * Test only one base currency allowed.
     */
    public function test_only_one_base_currency_allowed(): void
    {
        // Attempt to set USD as base - should fail or auto-unset IDR
        $usd = Currency::where('code', 'USD')->first();
        $usd->update(['is_base' => true]);

        // Either USD is now base and IDR is not, or the operation should fail
        // Depends on your implementation - here we test mutual exclusivity
        $currencies = Currency::where('is_base', true)->get();
        $this->assertEquals(1, $currencies->count());
    }
}
