<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRack;
use App\Models\WarehouseBin;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use SimpleSoftwareIO\SimpleQrCode\Facades\QrCode;

class WarehouseBinController extends Controller
{
    /**
     * Display a listing of bins.
     */
    public function index(Request $request): View
    {
        $query = WarehouseBin::with(['rack.zone.warehouse']);

        // Filter by rack
        if ($request->filled('rack_id')) {
            $query->where('rack_id', $request->rack_id);
        }

        // Filter by zone (through rack)
        if ($request->filled('zone_id')) {
            $query->whereHas('rack', function ($q) use ($request) {
                $q->where('zone_id', $request->zone_id);
            });
        }

        // Filter by warehouse (through rack->zone)
        if ($request->filled('warehouse_id')) {
            $query->whereHas('rack.zone', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by priority
        if ($request->filled('pick_priority')) {
            $query->where('pick_priority', $request->pick_priority);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $bins = $query->orderBy('rack_id')
            ->orderBy('level')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        $racks = WarehouseRack::with('zone.warehouse')->active()->orderBy('code')->get();

        return view('bins.index', compact('bins', 'racks'));
    }

    /**
     * Show the form for creating a new bin.
     */
    public function create(Request $request): View
    {
        $racks = WarehouseRack::with('zone.warehouse')->active()->orderBy('code')->get();
        $selectedRackId = $request->rack_id;
        $priorities = ['high', 'medium', 'low'];

        return view('bins.create', compact('racks', 'selectedRackId', 'priorities'));
    }

    /**
     * Store a newly created bin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rack_id' => 'required|exists:warehouse_racks,id',
            'code' => 'required|string|max:30',
            'barcode' => 'nullable|string|max:50|unique:warehouse_bins,barcode',
            'level' => 'nullable|integer|min:1|max:50',
            'max_capacity' => 'nullable|integer|min:1',
            'pick_priority' => 'nullable|in:high,medium,low',
            'is_active' => 'boolean',
        ]);

        // Check unique code within rack
        $exists = WarehouseBin::where('rack_id', $validated['rack_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Bin code already exists in this rack.'),
            ]);
        }

        $validated['level'] = $validated['level'] ?? 1;
        $validated['pick_priority'] = $validated['pick_priority'] ?? 'medium';
        $validated['is_active'] = $request->boolean('is_active', true);

        // Auto-generate barcode if not provided
        if (empty($validated['barcode'])) {
            $rack = WarehouseRack::with('zone.warehouse')->find($validated['rack_id']);
            $validated['barcode'] = $this->generateBarcode($rack, $validated['code']);
        }

        $bin = WarehouseBin::create($validated);

        return redirect()
            ->route('bins.index', ['rack_id' => $bin->rack_id])
            ->with('status', __('Bin created successfully.'));
    }

    /**
     * Display the specified bin.
     */
    public function show(WarehouseBin $bin): View
    {
        $bin->load(['rack.zone.warehouse']);

        return view('bins.show', compact('bin'));
    }

    /**
     * Show the form for editing the specified bin.
     */
    public function edit(WarehouseBin $bin): View
    {
        $racks = WarehouseRack::with('zone.warehouse')->active()->orderBy('code')->get();
        $priorities = ['high', 'medium', 'low'];

        return view('bins.edit', compact('bin', 'racks', 'priorities'));
    }

    /**
     * Update the specified bin.
     */
    public function update(Request $request, WarehouseBin $bin): RedirectResponse
    {
        $validated = $request->validate([
            'rack_id' => 'required|exists:warehouse_racks,id',
            'code' => 'required|string|max:30',
            'barcode' => 'nullable|string|max:50|unique:warehouse_bins,barcode,' . $bin->id,
            'level' => 'nullable|integer|min:1|max:50',
            'max_capacity' => 'nullable|integer|min:1',
            'pick_priority' => 'nullable|in:high,medium,low',
            'is_active' => 'boolean',
        ]);

        // Check unique code within rack (excluding current)
        $exists = WarehouseBin::where('rack_id', $validated['rack_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $bin->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Bin code already exists in this rack.'),
            ]);
        }

        $validated['level'] = $validated['level'] ?? 1;
        $validated['pick_priority'] = $validated['pick_priority'] ?? 'medium';
        $validated['is_active'] = $request->boolean('is_active', true);

        $bin->update($validated);

        return redirect()
            ->route('bins.index')
            ->with('status', __('Bin updated successfully.'));
    }

    /**
     * Remove the specified bin.
     */
    public function destroy(WarehouseBin $bin): RedirectResponse
    {
        // In Phase B, check if bin has stock_locations
        // For now, allow deletion

        $bin->delete();

        return redirect()
            ->route('bins.index')
            ->with('status', __('Bin deleted successfully.'));
    }

    /**
     * Generate QR code for bin.
     */
    public function generateQrCode(WarehouseBin $bin): Response
    {
        $qrData = json_encode([
            'type' => 'bin',
            'id' => $bin->id,
            'barcode' => $bin->barcode,
            'path' => $bin->full_path,
        ]);

        $qrCode = QrCode::size(200)->generate($qrData);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Generate a unique barcode for the bin.
     */
    private function generateBarcode(WarehouseRack $rack, string $binCode): string
    {
        $prefix = 'BIN';
        $warehouseCode = $rack->zone->warehouse->code ?? 'WH';
        $timestamp = now()->format('ymdHis');
        
        return strtoupper("{$prefix}-{$warehouseCode}-{$timestamp}");
    }
}
