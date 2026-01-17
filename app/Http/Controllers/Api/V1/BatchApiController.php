<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InsufficientCapacityException;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\Warehouse;
use App\Services\BatchAllocationService;
use App\Services\BatchInboundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Batch API Controller v1
 * 
 * Provides RESTful API endpoints for batch-based stock operations.
 * All operations go through the TDD-tested services to ensure proper
 * audit trail and batch tracking.
 */
class BatchApiController extends Controller
{
    protected BatchInboundService $inboundService;
    protected BatchAllocationService $allocationService;

    public function __construct(
        BatchInboundService $inboundService,
        BatchAllocationService $allocationService
    ) {
        $this->inboundService = $inboundService;
        $this->allocationService = $allocationService;
    }

    /**
     * POST /api/v1/stock-in
     * 
     * Create a stock-in transaction with batch tracking.
     * 
     * @bodyParam warehouse_id int required Warehouse ID
     * @bodyParam items array required Array of items to stock in
     * @bodyParam items[].product_id int required Product ID
     * @bodyParam items[].quantity int required Quantity to stock in
     * @bodyParam items[].purchase_price float required Purchase price per unit
     * @bodyParam items[].batch_number string optional Custom batch number
     * @bodyParam items[].expiry_date date optional Expiry date (YYYY-MM-DD)
     * @bodyParam supplier_id int optional Supplier ID
     * @bodyParam notes string optional Transaction notes
     */
    public function stockIn(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:50',
            'items.*.expiry_date' => 'nullable|date',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $user = Auth::user();
            $results = [];

            // Generate transaction code
            $date = now()->format('Ymd');
            $lastCode = StockIn::whereDate('date', now())->orderBy('transaction_code', 'desc')->first();
            $newNumber = $lastCode 
                ? str_pad((int)substr($lastCode->transaction_code, -3) + 1, 3, '0', STR_PAD_LEFT)
                : '001';
            $transactionCode = "IN-{$date}-{$newNumber}";

            // Calculate total
            $total = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['purchase_price']);

            // Create stock in header
            $stockIn = StockIn::create([
                'transaction_code' => $transactionCode,
                'date' => now(),
                'warehouse_id' => $warehouse->id,
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['purchase_price'];

                // Create stock in detail
                $detail = StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'total' => $itemTotal,
                ]);

                // Generate batch number if not provided
                $batchNumber = $item['batch_number'] ?? sprintf(
                    'B-%s-%s-%s',
                    $product->code,
                    now()->format('Ymd'),
                    str_pad(Batch::whereDate('created_at', now())->count() + 1, 3, '0', STR_PAD_LEFT)
                );

                // Create batch
                $batch = Batch::create([
                    'batch_number' => $batchNumber,
                    'product_id' => $product->id,
                    'supplier_id' => $request->supplier_id,
                    'manufacture_date' => $item['manufacture_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'cost_price' => $item['purchase_price'],
                    'status' => 'active',
                    'stock_in_id' => $stockIn->id,
                    'notes' => $request->notes,
                ]);

                // Use BatchInboundService for putaway (TDD-tested)
                $locations = $this->inboundService->putaway(
                    $batch,
                    $warehouse,
                    $item['quantity'],
                    $user,
                    $stockIn
                );

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'batch_number' => $batch->batch_number,
                    'batch_id' => $batch->id,
                    'quantity' => $item['quantity'],
                    'locations' => $locations->map(fn($loc) => [
                        'bin_code' => $loc['bin']->code,
                        'quantity' => $loc['quantity'],
                    ])->toArray(),
                ];

                Log::info('API Stock In: Batch created via BatchInboundService', [
                    'batch_id' => $batch->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'stock_in_id' => $stockIn->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock in created successfully',
                'data' => [
                    'stock_in_id' => $stockIn->id,
                    'transaction_code' => $transactionCode,
                    'total' => $total,
                    'items' => $results,
                ],
            ], 201);

        } catch (InsufficientCapacityException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'insufficient_capacity',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Stock In failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'Failed to create stock in: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/v1/stock-out
     * 
     * Create a stock-out transaction with batch allocation (FIFO/LIFO/FEFO).
     * 
     * @bodyParam warehouse_id int required Warehouse ID
     * @bodyParam items array required Array of items to stock out
     * @bodyParam items[].product_id int required Product ID
     * @bodyParam items[].quantity int required Quantity to stock out
     * @bodyParam items[].selling_price float required Selling price per unit
     * @bodyParam customer string optional Customer name
     * @bodyParam notes string optional Transaction notes
     */
    public function stockOut(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'customer' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $user = Auth::user();
            $results = [];

            // Generate transaction code
            $date = now()->format('Ymd');
            $lastCode = StockOut::whereDate('date', now())->orderBy('transaction_code', 'desc')->first();
            $newNumber = $lastCode 
                ? str_pad((int)substr($lastCode->transaction_code, -3) + 1, 3, '0', STR_PAD_LEFT)
                : '001';
            $transactionCode = "OUT-{$date}-{$newNumber}";

            // Calculate total
            $total = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['selling_price']);

            // Create stock out header
            $stockOut = StockOut::create([
                'transaction_code' => $transactionCode,
                'date' => now(),
                'warehouse_id' => $warehouse->id,
                'customer' => $request->customer,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['selling_price'];

                // Create stock out detail
                $detail = StockOutDetail::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'total' => $itemTotal,
                ]);

                // Use BatchAllocationService for allocation (TDD-tested FIFO/LIFO/FEFO)
                $allocations = $this->allocationService->allocate(
                    $product,
                    $warehouse,
                    $item['quantity'],
                    null, // No specific batch - use FIFO/LIFO/FEFO
                    $user,
                    $stockOut
                );

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'allocations' => $allocations->map(fn($alloc) => [
                        'batch_number' => $alloc['batch']->batch_number,
                        'batch_id' => $alloc['batch']->id,
                        'quantity' => $alloc['quantity'],
                        'bin_code' => $alloc['bin']->code,
                    ])->toArray(),
                ];

                Log::info('API Stock Out: Allocated via BatchAllocationService', [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'stock_out_id' => $stockOut->id,
                    'allocation_method' => $product->batch_method ?? 'FIFO',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock out created successfully',
                'data' => [
                    'stock_out_id' => $stockOut->id,
                    'transaction_code' => $transactionCode,
                    'total' => $total,
                    'items' => $results,
                ],
            ], 201);

        } catch (InsufficientStockException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'insufficient_stock',
                'message' => $e->getMessage(),
                'available' => $e->getAvailableQuantity(),
                'requested' => $e->getRequestedQuantity(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Stock Out failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'Failed to create stock out: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/inventory/batches
     * 
     * Get consolidated batch inventory with stock levels.
     * 
     * @queryParam warehouse_id int Filter by warehouse
     * @queryParam product_id int Filter by product
     * @queryParam status string Filter by batch status (active, expired, depleted)
     */
    public function getBatches(Request $request): JsonResponse
    {
        $query = Batch::with(['product', 'stockLocations.bin.rack.zone.warehouse'])
            ->withSum('stockLocations', 'quantity')
            ->withSum('stockLocations', 'reserved_quantity');

        if ($request->warehouse_id) {
            $warehouseId = $request->warehouse_id;
            $query->whereHas('stockLocations.bin.rack.zone', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $batches->map(fn($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'product' => [
                    'id' => $batch->product->id,
                    'code' => $batch->product->code,
                    'name' => $batch->product->name,
                ],
                'manufacture_date' => $batch->manufacture_date?->format('Y-m-d'),
                'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                'cost_price' => $batch->cost_price,
                'status' => $batch->status,
                'total_quantity' => (int)$batch->stock_locations_sum_quantity ?? 0,
                'reserved_quantity' => (int)$batch->stock_locations_sum_reserved_quantity ?? 0,
                'available_quantity' => ((int)$batch->stock_locations_sum_quantity ?? 0) 
                    - ((int)$batch->stock_locations_sum_reserved_quantity ?? 0),
                'is_expired' => $batch->is_expired,
                'locations' => $batch->stockLocations->map(fn($loc) => [
                    'bin_code' => $loc->bin->code,
                    'warehouse' => $loc->bin->rack->zone->warehouse->name,
                    'quantity' => $loc->quantity,
                    'reserved' => $loc->reserved_quantity ?? 0,
                ]),
            ]),
            'meta' => [
                'current_page' => $batches->currentPage(),
                'last_page' => $batches->lastPage(),
                'per_page' => $batches->perPage(),
                'total' => $batches->total(),
            ],
        ]);
    }
}
