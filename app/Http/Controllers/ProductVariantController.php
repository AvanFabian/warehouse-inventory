<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    /**
     * Show variants for a specific product
     */
    public function index(Product $product)
    {
        $variants = $product->variants()->with('warehouses')->get();
        return view('products.variants.index', compact('product', 'variants'));
    }

    /**
     * Show the form for creating a new variant
     */
    public function create(Product $product)
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('products.variants.create', compact('product', 'warehouses'));
    }

    /**
     * Store a newly created variant
     */
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'variant_name' => 'required|string',
            'sku_suffix' => 'nullable|string|max:50',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'rack_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Generate variant code
        $skuSuffix = $data['sku_suffix'] ?? Str::random(6);
        $variantCode = $product->code . '-' . strtoupper($skuSuffix);

        // Check if variant code is unique
        if (ProductVariant::where('variant_code', $variantCode)->exists()) {
            return back()->withErrors(['sku_suffix' => 'This variant code already exists'])->withInput();
        }

        $warehouseId = $data['warehouse_id'];
        $initialStock = $data['stock'] ?? 0;
        $rackLocation = $data['rack_location'] ?? null;

        // Remove warehouse-specific fields
        unset($data['warehouse_id'], $data['stock'], $data['rack_location']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products/variants', $filename, 'public');
            $data['image'] = $path;
        }

        // Clean attributes (remove empty values)
        if (isset($data['attributes'])) {
            $data['attributes'] = array_filter($data['attributes'], fn($value) => !empty($value));
        }

        // Create variant
        $variant = $product->variants()->create([
            'variant_code' => $variantCode,
            'variant_name' => $data['variant_name'],
            'sku_suffix' => $skuSuffix,
            'attributes' => $data['attributes'] ?? null,
            'purchase_price' => $data['purchase_price'] ?? null,
            'selling_price' => $data['selling_price'] ?? null,
            'image' => $data['image'] ?? null,
            'status' => true,
        ]);

        // Attach to warehouse with initial stock
        // Must include product_id since product_warehouse table requires it
        $variant->warehouses()->attach($warehouseId, [
            'product_id' => $product->id,
            'stock' => $initialStock,
            'rack_location' => $rackLocation,
            'min_stock' => null
        ]);

        // Mark product as having variants
        if (!$product->has_variants) {
            $product->update(['has_variants' => true]);
        }

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Product variant created successfully');
    }

    /**
     * Show the form for editing a variant
     */
    public function edit(Product $product, ProductVariant $variant)
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $variant->load('warehouses');
        return view('products.variants.edit', compact('product', 'variant', 'warehouses'));
    }

    /**
     * Update the specified variant
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $data = $request->validate([
            'variant_name' => 'required|string',
            'attributes' => 'nullable|array',
            'attributes.*' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products/variants', $filename, 'public');
            $data['image'] = $path;
        }

        // Clean attributes
        if (isset($data['attributes'])) {
            $data['attributes'] = array_filter($data['attributes'], fn($value) => !empty($value));
        }

        $variant->update($data);

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Product variant updated successfully');
    }

    /**
     * Remove the specified variant
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        // Delete image if exists
        if ($variant->image) {
            Storage::disk('public')->delete($variant->image);
        }

        $variant->delete();

        // If no more variants, disable has_variants flag
        if ($product->variants()->count() === 0) {
            $product->update(['has_variants' => false]);
        }

        return redirect()->route('products.variants.index', $product)
            ->with('success', 'Product variant deleted successfully');
    }
}
