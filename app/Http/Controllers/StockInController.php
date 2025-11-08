<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
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
                'supplier_id' => $request->supplier_id,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Create details and update product stock
            foreach ($request->products as $item) {
                $itemTotal = $item['quantity'] * $item['purchase_price'];

                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->stock += $item['quantity'];
                $product->save();
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
            // Revert product stock
            foreach ($stockIn->details as $detail) {
                $product = Product::find($detail->product_id);
                $product->stock -= $detail->quantity;
                $product->save();
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
