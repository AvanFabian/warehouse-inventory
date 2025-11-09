<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $warehouses = Warehouse::when($q, fn($query) => $query->where('name', 'like', "%{$q}%")
            ->orWhere('code', 'like', "%{$q}%")
            ->orWhere('city', 'like', "%{$q}%"))
            ->withCount(['products', 'stockIns', 'stockOuts'])
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('warehouses.index', compact('warehouses', 'q'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Handle checkboxes separately - checkboxes only send value when checked
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        $data['created_by'] = Auth::id();

        // If setting as default, this warehouse must be active
        if ($data['is_default'] && !$data['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gudang default harus dalam status aktif.');
        }

        // If this warehouse is set as default, unset other defaults
        if ($data['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
            // Ensure default warehouse is always active
            $data['is_active'] = true;
        }

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('status', 'Gudang berhasil dibuat');
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        // Handle checkboxes separately - checkboxes only send value when checked
        $data['is_active'] = $request->has('is_active');
        $data['is_default'] = $request->has('is_default');
        $data['updated_by'] = Auth::id();

        // Prevent deactivating the default warehouse
        if ($warehouse->is_default && !$data['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gudang default tidak dapat dinonaktifkan. Ubah gudang default terlebih dahulu.');
        }

        // Prevent removing default status if there's only one active warehouse
        if ($warehouse->is_default && !$data['is_default']) {
            $otherActiveWarehouses = Warehouse::where('id', '!=', $warehouse->id)
                ->where('is_active', true)
                ->count();

            if ($otherActiveWarehouses == 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Harus ada minimal satu gudang default yang aktif. Tetapkan gudang lain sebagai default terlebih dahulu.');
            }
        }

        // If setting as default, this warehouse must be active
        if ($data['is_default'] && !$data['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gudang default harus dalam status aktif.');
        }

        // If this warehouse is set as default, unset other defaults and make this one active
        if ($data['is_default']) {
            Warehouse::where('id', '!=', $warehouse->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Ensure default warehouse is always active
            $data['is_active'] = true;
        }

        $warehouse->update($data);

        return redirect()->route('warehouses.index')->with('status', 'Gudang berhasil diperbarui');
    }

    public function destroy(Warehouse $warehouse)
    {
        try {
            $warehouse->delete();
            return redirect()->route('warehouses.index')->with('status', 'Warehouse deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('warehouses.index')->with('error', $e->getMessage());
        }
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['products' => function ($query) {
            $query->orderBy('name');
        }]);

        $totalStockValue = $warehouse->getTotalStockValue();
        $productCount = $warehouse->getProductCount();
        $stockInsCount = $warehouse->stockIns()->count();
        $stockOutsCount = $warehouse->stockOuts()->count();
        $transfersFromCount = $warehouse->transfersFrom()->count();
        $transfersToCount = $warehouse->transfersTo()->count();

        return view('warehouses.show', compact(
            'warehouse',
            'totalStockValue',
            'productCount',
            'stockInsCount',
            'stockOutsCount',
            'transfersFromCount',
            'transfersToCount'
        ));
    }
}
