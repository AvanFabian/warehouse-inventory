<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseZoneController extends Controller
{
    /**
     * Display a listing of zones.
     */
    public function index(Request $request): View
    {
        $query = WarehouseZone::with('warehouse', 'racks');

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $zones = $query->orderBy('warehouse_id')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('zones.index', compact('zones', 'warehouses'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create(): View
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $types = ['storage', 'receiving', 'shipping', 'quarantine', 'returns'];

        return view('zones.create', compact('warehouses', 'types'));
    }

    /**
     * Store a newly created zone.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'type' => 'required|in:storage,receiving,shipping,quarantine,returns',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Check unique code within warehouse
        $exists = WarehouseZone::where('warehouse_id', $validated['warehouse_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Zone code already exists in this warehouse.'),
            ]);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $zone = WarehouseZone::create($validated);

        return redirect()
            ->route('zones.index')
            ->with('status', __('Zone created successfully.'));
    }

    /**
     * Display the specified zone.
     */
    public function show(WarehouseZone $zone): View
    {
        $zone->load(['warehouse', 'racks.bins']);

        return view('zones.show', compact('zone'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(WarehouseZone $zone): View
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $types = ['storage', 'receiving', 'shipping', 'quarantine', 'returns'];

        return view('zones.edit', compact('zone', 'warehouses', 'types'));
    }

    /**
     * Update the specified zone.
     */
    public function update(Request $request, WarehouseZone $zone): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:100',
            'type' => 'required|in:storage,receiving,shipping,quarantine,returns',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Check unique code within warehouse (excluding current)
        $exists = WarehouseZone::where('warehouse_id', $validated['warehouse_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $zone->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Zone code already exists in this warehouse.'),
            ]);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $zone->update($validated);

        return redirect()
            ->route('zones.index')
            ->with('status', __('Zone updated successfully.'));
    }

    /**
     * Remove the specified zone.
     */
    public function destroy(WarehouseZone $zone): RedirectResponse
    {
        // Check if zone has racks
        if ($zone->racks()->count() > 0) {
            return back()->withErrors([
                'error' => __('Cannot delete zone with existing racks. Please delete all racks first.'),
            ]);
        }

        $zone->delete();

        return redirect()
            ->route('zones.index')
            ->with('status', __('Zone deleted successfully.'));
    }
}
