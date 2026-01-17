<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\Product;
use App\Services\LegacyStockAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $stockOuts = StockOut::query()
            ->when($q, fn($query) => $query->where('transaction_code', 'like', "%{$q}%")->orWhere('customer', 'like', "%{$q}%"))
            ->when($dateFrom, fn($query) => $query->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('date', '<=', $dateTo))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('stock-outs.index', compact('stockOuts', 'q', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $warehouses = \App\Models\Warehouse::active()->orderBy('name')->get();

        // Generate transaction code
        $date = date('Ymd');
        $lastCode = StockOut::whereDate('date', today())->orderBy('transaction_code', 'desc')->first();

        if ($lastCode) {
            $lastNumber = (int) substr($lastCode->transaction_code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $transactionCode = "OUT-{$date}-{$newNumber}";

        return view('stock-outs.create', compact('warehouses', 'transactionCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer' => 'nullable|string',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.selling_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Validate stock availability in the pivot table for the specific warehouse
            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check if product exists in this warehouse
                if (!$product->warehouses()->where('warehouse_id', $request->warehouse_id)->exists()) {
                    throw new \Exception("Product {$product->name} not found in selected warehouse");
                }

                // Get stock from pivot table
                $stock = $product->getStockInWarehouse($request->warehouse_id);

                if ($stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$stock}, Requested: {$item['quantity']}");
                }
            }

            // Generate transaction code
            $date = date('Ymd', strtotime($request->date));
            $lastCode = StockOut::whereDate('date', $request->date)->orderBy('transaction_code', 'desc')->first();

            if ($lastCode) {
                $lastNumber = (int) substr($lastCode->transaction_code, -3);
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }

            $transactionCode = "OUT-{$date}-{$newNumber}";

            // Calculate total
            $total = 0;
            foreach ($request->products as $item) {
                $total += $item['quantity'] * $item['selling_price'];
            }

            // Create stock out header
            $stockOut = StockOut::create([
                'transaction_code' => $transactionCode,
                'date' => $request->date,
                'warehouse_id' => $request->warehouse_id,
                'customer' => $request->customer,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Create details and update product stock in pivot table for the specific warehouse
            foreach ($request->products as $item) {
                $itemTotal = $item['quantity'] * $item['selling_price'];

                StockOutDetail::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock in pivot table for the specific warehouse
                $product = Product::findOrFail($item['product_id']);

                if ($product->warehouses()->where('warehouse_id', $request->warehouse_id)->exists()) {
                    // Decrease stock using DB::raw to prevent race conditions
                    $product->warehouses()->updateExistingPivot($request->warehouse_id, [
                        'stock' => DB::raw('stock - ' . (int)$item['quantity'])
                    ]);
                    
                    // Bridge logging for legacy stock update
                    LegacyStockAuditService::logPivotStockChange(
                        $item['product_id'],
                        $request->warehouse_id,
                        -(int)$item['quantity'],
                        'stock_out',
                        ['transaction_code' => $stockOut->transaction_code, 'stock_out_id' => $stockOut->id]
                    );
                }
            }

            DB::commit();
            return redirect()->route('stock-outs.show', $stockOut)->with('status', 'Stock Out created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to create stock out: ' . $e->getMessage()]);
        }
    }

    public function show(StockOut $stockOut)
    {
        $stockOut->load(['details.product.category']);
        return view('stock-outs.show', compact('stockOut'));
    }

    public function destroy(StockOut $stockOut)
    {
        DB::beginTransaction();
        try {
            // Revert product stock in pivot table for the specific warehouse
            foreach ($stockOut->details as $detail) {
                $product = Product::find($detail->product_id);

                if ($product && $product->warehouses()->where('warehouse_id', $stockOut->warehouse_id)->exists()) {
                    // Increase stock using DB::raw
                    $product->warehouses()->updateExistingPivot($stockOut->warehouse_id, [
                        'stock' => DB::raw('stock + ' . (int)$detail->quantity)
                    ]);
                    
                    // Bridge logging for legacy stock reversal
                    LegacyStockAuditService::logPivotStockChange(
                        $detail->product_id,
                        $stockOut->warehouse_id,
                        (int)$detail->quantity,
                        'stock_out_delete',
                        ['transaction_code' => $stockOut->transaction_code, 'stock_out_id' => $stockOut->id]
                    );
                }
            }

            $stockOut->delete();
            DB::commit();

            return redirect()->route('stock-outs.index')->with('status', 'Stock Out deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete stock out: ' . $e->getMessage()]);
        }
    }

    public function getProductStock($productId)
    {
        $warehouseId = request('warehouse_id');
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'stock' => 0,
                'unit' => '',
                'selling_price' => 0,
            ]);
        }

        // Get stock from pivot table
        $stock = $product->getStockInWarehouse($warehouseId);

        return response()->json([
            'stock' => $stock,
            'unit' => $product->unit,
            'selling_price' => $product->selling_price,
        ]);
    }

    public function getWarehouseProducts($warehouseId)
    {
        // Get products that have stock in this warehouse via pivot table
        $products = Product::whereHas('warehouses', function ($query) use ($warehouseId) {
            $query->where('warehouse_id', $warehouseId)
                ->where('product_warehouse.stock', '>', 0);
        })
            ->where('status', true)
            ->with(['warehouses' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $warehouse = $product->warehouses->first();
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'stock' => $warehouse ? $warehouse->pivot->stock : 0,
                    'unit' => $product->unit,
                    'selling_price' => $product->selling_price,
                ];
            });

        return response()->json($products);
    }
}