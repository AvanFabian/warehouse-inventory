<?php

namespace App\Http\Controllers;

use App\Models\StockOut;
use App\Models\StockOutDetail;
use App\Models\Product;
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
        $products = Product::where('status', true)->where('stock', '>', 0)->orderBy('name')->get();

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

        return view('stock-outs.create', compact('products', 'transactionCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'customer' => 'nullable|string',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.selling_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Validate stock availability
            foreach ($request->products as $item) {
                $product = Product::find($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}");
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
                'customer' => $request->customer,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Create details and update product stock
            foreach ($request->products as $item) {
                $itemTotal = $item['quantity'] * $item['selling_price'];

                StockOutDetail::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'total' => $itemTotal,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                $product->stock -= $item['quantity'];
                $product->save();
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
            // Revert product stock
            foreach ($stockOut->details as $detail) {
                $product = Product::find($detail->product_id);
                $product->stock += $detail->quantity;
                $product->save();
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
        $product = Product::find($productId);
        return response()->json([
            'stock' => $product ? $product->stock : 0,
            'unit' => $product ? $product->unit : '',
            'selling_price' => $product ? $product->selling_price : 0,
        ]);
    }
}
