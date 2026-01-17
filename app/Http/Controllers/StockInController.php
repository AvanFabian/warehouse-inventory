<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\LegacyStockAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $stockIns = StockIn::with(['supplier', 'warehouse'])
            ->when($q, fn($query) => $query->where('transaction_code', 'like', "%{$q}%"))
            ->when($supplierId, fn($query) => $query->where('supplier_id', $supplierId))
            ->when($warehouseId, fn($query) => $query->where('warehouse_id', $warehouseId))
            ->when($dateFrom, fn($query) => $query->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('date', '<=', $dateTo))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('stock-ins.index', compact('stockIns', 'suppliers', 'warehouses', 'q', 'supplierId', 'warehouseId', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        // Get all active products grouped by warehouse for reference
        // In the view, we'll filter by selected warehouse
        $products = Product::where('status', true)->orderBy('name')->get();

        // Generate transaction code
        $date = date('Ymd');
        $lastCode = StockIn::whereDate('date', today())->orderBy('transaction_code', 'desc')->first();

        if ($lastCode) {
            $lastNumber = (int) substr($lastCode->transaction_code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $transactionCode = "IN-{$date}-{$newNumber}";

        return view('stock-ins.create', compact('suppliers', 'warehouses', 'products', 'transactionCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Generate transaction code
            $date = date('Ymd', strtotime($request->date));
            $lastCode = StockIn::whereDate('date', $request->date)->orderBy('transaction_code', 'desc')->first();

            if ($lastCode) {
                $lastNumber = (int) substr($lastCode->transaction_code, -3);
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $transactionCode = "IN-{$date}-{$newNumber}";

            // Calculate total
            $total = 0;
            foreach ($request->products as $item) {
                $total += $item['quantity'] * $item['purchase_price'];
            }

            // Create stock in header
            $stockIn = StockIn::create([
                'transaction_code' => $transactionCode,
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Create details and update product stock for the specific warehouse
            foreach ($request->products as $item) {
                $itemTotal = $item['quantity'] * $item['purchase_price'];

                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock in the pivot table for the specific warehouse
                $product = Product::findOrFail($item['product_id']);

                // Check if product is already assigned to this warehouse
                if ($product->warehouses()->where('warehouse_id', $request->warehouse_id)->exists()) {
                    // Update existing stock using DB::raw to prevent race conditions
                    $product->warehouses()->updateExistingPivot($request->warehouse_id, [
                        'stock' => DB::raw('stock + ' . (int)$item['quantity'])
                    ]);
                    
                    // Bridge logging for legacy stock update
                    LegacyStockAuditService::logPivotStockChange(
                        $item['product_id'],
                        $request->warehouse_id,
                        (int)$item['quantity'],
                        'stock_in',
                        ['transaction_code' => $stockIn->transaction_code, 'stock_in_id' => $stockIn->id]
                    );
                } else {
                    // Attach product to warehouse with initial stock
                    $product->warehouses()->attach($request->warehouse_id, [
                        'stock' => $item['quantity'],
                        'rack_location' => null,
                        'min_stock' => null
                    ]);
                    
                    // Bridge logging for legacy stock update (new product-warehouse association)
                    LegacyStockAuditService::logPivotStockChange(
                        $item['product_id'],
                        $request->warehouse_id,
                        (int)$item['quantity'],
                        'stock_in_attach',
                        ['transaction_code' => $stockIn->transaction_code, 'stock_in_id' => $stockIn->id]
                    );
                }
            }

            DB::commit();
            return redirect()->route('stock-ins.show', $stockIn)->with('status', 'Stock In created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create stock in: ' . $e->getMessage()]);
        }
    }

    public function show(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'details.product.category']);
        return view('stock-ins.show', compact('stockIn'));
    }

    public function destroy(StockIn $stockIn)
    {
        DB::beginTransaction();
        try {
            // Revert product stock in the pivot table for the specific warehouse
            foreach ($stockIn->details as $detail) {
                $product = Product::find($detail->product_id);

                if ($product && $product->warehouses()->where('warehouse_id', $stockIn->warehouse_id)->exists()) {
                    // Decrease stock using DB::raw
                    $product->warehouses()->updateExistingPivot($stockIn->warehouse_id, [
                        'stock' => DB::raw('stock - ' . (int)$detail->quantity)
                    ]);
                    
                    // Bridge logging for legacy stock reversal
                    LegacyStockAuditService::logPivotStockChange(
                        $detail->product_id,
                        $stockIn->warehouse_id,
                        -(int)$detail->quantity,
                        'stock_in_delete',
                        ['transaction_code' => $stockIn->transaction_code, 'stock_in_id' => $stockIn->id]
                    );
                }
            }

            $stockIn->delete();
            DB::commit();

            return redirect()->route('stock-ins.index')->with('status', 'Stock In deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete stock in: ' . $e->getMessage()]);
        }
    }
}
