<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categoryId = $request->query('category_id');
        $status = $request->query('status');

        $products = Product::with('category')
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId))
            ->when($status !== null, fn($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::where('status', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'q', 'categoryId', 'status'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:products,code',
            'name' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string',
            'min_stock' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'rack_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status');
        $data['stock'] = 0; // initial stock is 0

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image'] = $path;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('status', 'Product created successfully');
    }

    public function show(Product $product)
    {
        $product->load('category');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:products,code,' . $product->id,
            'name' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string',
            'min_stock' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'rack_location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('products', $filename, 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('status', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return redirect()->route('products.index')->with('status', 'Product deleted successfully');
    }

    public function import(Request $request)
    {
        // TODO: Implement CSV import using maatwebsite/excel
        return back()->with('status', 'Import feature coming soon');
    }

    public function export(Request $request)
    {
        // TODO: Implement Excel export using maatwebsite/excel
        return back()->with('status', 'Export feature coming soon');
    }
}
