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
        $products = Product::where('status', true)->orderBy('name')->get();
        return view('stock-opnames.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'counted_qty' => 'required|integer|min:0',
            'reason' => 'required|string',
            'date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::find($request->product_id);
            $systemQty = $product->stock;
            $countedQty = $request->counted_qty;
            $difference = $countedQty - $systemQty;

            // Create opname record
            StockOpname::create([
                'product_id' => $request->product_id,
                'system_qty' => $systemQty,
                'counted_qty' => $countedQty,
                'difference' => $difference,
                'reason' => $request->reason,
                'date' => $request->date,
                'user_id' => Auth::id(),
            ]);

            // Update product stock
            $product->stock = $countedQty;
            $product->save();

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
