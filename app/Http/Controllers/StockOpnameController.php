<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $productId = $request->query('product_id');

        $opnames = StockOpname::with(['product', 'user'])
            ->when($dateFrom, fn($query) => $query->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('date', '<=', $dateTo))
            ->when($productId, fn($query) => $query->where('product_id', $productId))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $products = Product::where('status', true)->orderBy('name')->get();

        return view('stock-opnames.index', compact('opnames', 'products', 'dateFrom', 'dateTo', 'productId'));
    }

    public function create()
    {
        $warehouses = \App\Models\Warehouse::active()->orderBy('name')->get();
        $products = Product::where('status', true)->orderBy('name')->get();
        return view('stock-opnames.create', compact('products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'counted_qty' => 'required|integer|min:0',
            'reason' => 'required|string',
            'date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($request->product_id);
            
            // Get current stock from pivot table for the specified warehouse
            $systemQty = $product->getStockInWarehouse($request->warehouse_id);
            $countedQty = $request->counted_qty;
            $difference = $countedQty - $systemQty;

            // Create opname record
            StockOpname::create([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'system_qty' => $systemQty,
                'counted_qty' => $countedQty,
                'difference' => $difference,
                'reason' => $request->reason,
                'date' => $request->date,
                'user_id' => Auth::id(),
            ]);

            // Update product stock in pivot table for the warehouse
            if ($product->warehouses()->where('warehouse_id', $request->warehouse_id)->exists()) {
                // Update existing
                $product->warehouses()->updateExistingPivot($request->warehouse_id, [
                    'stock' => $countedQty
                ]);
            } else {
                // Attach if not exists
                $product->warehouses()->attach($request->warehouse_id, [
                    'stock' => $countedQty,
                    'rack_location' => null,
                    'min_stock' => null
                ]);
            }

            DB::commit();
            return redirect()->route('stock-opnames.index')->with('status', 'Stock Opname recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to record stock opname: ' . $e->getMessage()]);
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        $stockOpname->delete();
        return redirect()->route('stock-opnames.index')->with('status', 'Stock Opname deleted successfully');
    }
}