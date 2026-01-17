<?php

namespace App\Http\Controllers;

use App\Models\WarehouseZone;
use App\Models\WarehouseRack;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseRackController extends Controller
{
    /**
     * Display a listing of racks.
     */
    public function index(Request $request): View
    {
        $query = WarehouseRack::with(['zone.warehouse', 'bins']);

        // Filter by zone
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        // Filter by warehouse (through zone)
        if ($request->filled('warehouse_id')) {
            $query->whereHas('zone', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
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

        $racks = $query->orderBy('zone_id')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        $zones = WarehouseZone::with('warehouse')->active()->orderBy('name')->get();

        return view('racks.index', compact('racks', 'zones'));
    }

    /**
     * Show the form for creating a new rack.
     */
    public function create(Request $request): View
    {
        $zones = WarehouseZone::with('warehouse')->active()->orderBy('name')->get();
        $selectedZoneId = $request->zone_id;

        return view('racks.create', compact('zones', 'selectedZoneId'));
    }

    /**
     * Store a newly created rack.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:warehouse_zones,id',
            'code' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'levels' => 'nullable|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        // Check unique code within zone
        $exists = WarehouseRack::where('zone_id', $validated['zone_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Rack code already exists in this zone.'),
            ]);
        }

        $validated['levels'] = $validated['levels'] ?? 1;
        $validated['is_active'] = $request->boolean('is_active', true);

        $rack = WarehouseRack::create($validated);

        return redirect()
            ->route('racks.index', ['zone_id' => $rack->zone_id])
            ->with('status', __('Rack created successfully.'));
    }

    /**
     * Display the specified rack.
     */
    public function show(WarehouseRack $rack): View
    {
        $rack->load(['zone.warehouse', 'bins']);

        return view('racks.show', compact('rack'));
    }

    /**
     * Show the form for editing the specified rack.
     */
    public function edit(WarehouseRack $rack): View
    {
        $zones = WarehouseZone::with('warehouse')->active()->orderBy('name')->get();

        return view('racks.edit', compact('rack', 'zones'));
    }

    /**
     * Update the specified rack.
     */
    public function update(Request $request, WarehouseRack $rack): RedirectResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|exists:warehouse_zones,id',
            'code' => 'required|string|max:20',
            'name' => 'nullable|string|max:100',
            'levels' => 'nullable|integer|min:1|max:20',
            'is_active' => 'boolean',
        ]);

        // Check unique code within zone (excluding current)
        $exists = WarehouseRack::where('zone_id', $validated['zone_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $rack->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'code' => __('Rack code already exists in this zone.'),
            ]);
        }

        $validated['levels'] = $validated['levels'] ?? 1;
        $validated['is_active'] = $request->boolean('is_active', true);

        $rack->update($validated);

        return redirect()
            ->route('racks.index')
            ->with('status', __('Rack updated successfully.'));
    }

    /**
     * Remove the specified rack.
     */
    public function destroy(WarehouseRack $rack): RedirectResponse
    {
        // Check if rack has bins
        if ($rack->bins()->count() > 0) {
            return back()->withErrors([
                'error' => __('Cannot delete rack with existing bins. Please delete all bins first.'),
            ]);
        }

        $rack->delete();

        return redirect()
            ->route('racks.index')
            ->with('status', __('Rack deleted successfully.'));
    }
}
