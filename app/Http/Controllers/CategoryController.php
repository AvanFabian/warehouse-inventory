<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $categories = Category::when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories', 'q'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
        ]);

        // Handle checkbox - if checked it sends 'on', if unchecked it sends nothing
        $data['status'] = $request->has('status') ? true : false;

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dibuat');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id . ',id,deleted_at,NULL',
            'description' => 'nullable|string',
        ]);

        // Handle checkbox - if checked it sends 'on', if unchecked it sends nothing
        $data['status'] = $request->has('status') ? true : false;

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('status', 'Category deleted');
    }
}
