<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchMovement;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
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

        $opnames = StockOpname::with(['product', 'user', 'warehouse', 'batch', 'bin'])
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
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::with('warehouses')->where('status', true)->orderBy('name')->get();
        
        // Get active batches with stock
        $batches = Batch::where('status', 'active')
            ->whereHas('stockLocations', fn($q) => $q->where('quantity', '>', 0))
            ->with(['product', 'stockLocations.bin.rack.zone.warehouse'])
            ->get();

        // Get bins
        $bins = WarehouseBin::where('is_active', true)
            ->with('rack.zone.warehouse')
            ->get();

        return view('stock-opnames.create', compact('products', 'warehouses', 'batches', 'bins'));
    }

    /**
     * Store stock opname - synchronized with batch architecture.
     * 
     * This method:
     * 1. Updates stock_locations (not product_warehouse pivot)
     * 2. Creates BatchMovement with type 'adjustment'
     * 3. Triggers AuditLog entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'batch_id' => 'required|exists:batches,id',
            'bin_id' => 'required|exists:warehouse_bins,id',
            'counted_qty' => 'required|integer|min:0',
            'reason' => 'required|string|min:10',
            'date' => 'required|date',
        ], [
            'reason.required' => 'A reason for the adjustment is mandatory.',
            'reason.min' => 'Please provide a detailed reason (at least 10 characters).',
            'counted_qty.min' => 'Counted quantity must be a positive integer.',
        ]);

        DB::beginTransaction();
        try {
            $batch = Batch::findOrFail($request->batch_id);
            $bin = WarehouseBin::findOrFail($request->bin_id);
            
            // Find or create stock location for this batch+bin
            $stockLocation = StockLocation::firstOrCreate(
                ['batch_id' => $batch->id, 'bin_id' => $bin->id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $systemQty = $stockLocation->quantity;
            $countedQty = $request->counted_qty;
            $difference = $countedQty - $systemQty;

            // Create opname record
            $opname = StockOpname::create([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'batch_id' => $batch->id,
                'bin_id' => $bin->id,
                'system_qty' => $systemQty,
                'counted_qty' => $countedQty,
                'difference' => $difference,
                'reason' => $request->reason,
                'date' => $request->date,
                'user_id' => Auth::id(),
            ]);

            // Update stock_location (NOT the legacy pivot table)
            $stockLocation->update(['quantity' => $countedQty]);

            // Create BatchMovement for traceability
            $movementData = [
                'batch_id' => $batch->id,
                'quantity' => abs($difference),
                'movement_type' => 'adjustment',
                'reference_type' => StockOpname::class,
                'reference_id' => $opname->id,
                'performed_by' => Auth::id(),
                'notes' => "Stock Opname Adjustment: {$request->reason}",
            ];

            if ($difference < 0) {
                // Stock decreased - source is the bin
                $movementData['source_bin_id'] = $bin->id;
            } else {
                // Stock increased - destination is the bin
                $movementData['destination_bin_id'] = $bin->id;
            }

            if ($difference != 0) {
                BatchMovement::create($movementData);
            }

            // AuditLog is automatically triggered by model observers

            // Also update legacy pivot for compatibility
            $product = Product::findOrFail($request->product_id);
            $this->syncLegacyPivot($product, $request->warehouse_id);

            DB::commit();
            return redirect()->route('stock-opnames.index')
                ->with('success', 'Stock Opname recorded successfully. ' . 
                    ($difference != 0 ? "Adjusted by {$difference} units." : "No adjustment needed."));
                    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('StockOpname error: ' . $e->getMessage());
            return back()->withInput()
                ->withErrors(['error' => 'Failed to record stock opname: ' . $e->getMessage()]);
        }
    }

    /**
     * Sync the legacy product_warehouse pivot with actual stock from stock_locations.
     */
    private function syncLegacyPivot(Product $product, int $warehouseId): void
    {
        // Calculate total stock from stock_locations for this product in this warehouse
        $totalStock = StockLocation::whereHas('batch', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->whereHas('bin.rack.zone', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->sum('quantity');

        if ($product->warehouses()->where('warehouse_id', $warehouseId)->exists()) {
            $product->warehouses()->updateExistingPivot($warehouseId, [
                'stock' => $totalStock
            ]);
        } else {
            $product->warehouses()->attach($warehouseId, [
                'stock' => $totalStock,
                'min_stock' => $product->min_stock,
            ]);
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        $stockOpname->delete();
        return redirect()->route('stock-opnames.index')
            ->with('success', 'Stock Opname deleted successfully');
    }
}
