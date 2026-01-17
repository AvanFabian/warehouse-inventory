<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    /**
     * Display a listing of batches.
     */
    public function index(Request $request)
    {
        $query = Batch::with(['product', 'stockLocations.bin.rack.zone.warehouse'])
            ->withCount('stockLocations');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product')) {
            $query->where('product_id', $request->product);
        }

        if ($request->filled('warehouse')) {
            $query->whereHas('stockLocations.bin.rack.zone', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse);
            });
        }

        if ($request->filled('expiring')) {
            $query->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays((int)$request->expiring));
        }

        if ($request->filled('search')) {
            $query->where('batch_number', 'like', '%' . $request->search . '%');
        }

        $batches = $query->latest()->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('batches.index', compact('batches', 'warehouses'));
    }

    /**
     * Display the specified batch.
     */
    public function show(Batch $batch)
    {
        $batch->load([
            'product',
            'stockLocations.bin.rack.zone.warehouse',
            'movements' => fn($q) => $q->latest()->take(20),
        ]);

        // Get audit trail for this batch
        $auditLogs = AuditLog::where('auditable_type', Batch::class)
            ->where('auditable_id', $batch->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return view('batches.show', compact('batch', 'auditLogs'));
    }
}
